<?php

namespace App\Filament\Pages;

use App\Exports\DailyRoomSheetExport;
use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\Expense;
use App\Models\Setting;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

class DailyRoomSheet extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.daily-room-sheet';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::Calendar;

    public $date;

    public $data = [];

    public $totalsRentByMonth = 0;

    public $totalExpenditure = 0;

    public $expenditures = [];

    public $dueBeforeToday = 0;

    public $card;

    public $selectedCardNo;

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->can('view DailyRoomSheet');
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Select Date')
                ->description('Select a date to generate the report')
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->default(now()->format('Y-m-d'))
                        ->maxDate(now())
                        ->native(false)
                        ->live(),
                ])
                ->footerActions([
                    Action::make('Generate Report')
                        ->color('success')
                        ->action(fn () => $this->loadData())
                        ->disabled(fn () => blank($this->date)),

                    Action::make('Download PDF')
                        ->url(fn () => $this->date
                            ? route('download.invoice', ['date' => Carbon::parse($this->date)->format('d-m-Y')])
                            : null)
                        ->openUrlInNewTab()
                        ->disabled(fn () => blank($this->date)),
                ]),
        ]);
    }

    // ─── DAILY COLLECTION LOGIC (previously in Livewire\DailyRoomSheet) ───

    public function showDailyCollection(string $dateIn)
    {
        $date = Carbon::parse($dateIn);
        $cards = $this->getCardsForDailySlices($dateIn);

        $filteredItems = collect();

        foreach ($cards as $card) {
            $calculator = new DailyCollectionCalculator($card);
            $dailyCollection = $calculator->calculate();

            $filteredItem = $dailyCollection->first(
                fn ($item) => Carbon::parse($item['specific_date'])->isSameDay($date)
            );

            if ($filteredItem) {
                $filteredItems->push($filteredItem);
            }
        }

        return $filteredItems;
    }

    protected function getCardsForDailySlices(string $dateIn)
    {
        $ciStart = Setting::where('key', 'check_in_start_time')->value('value');
        $ciEnd = Setting::where('key', 'check_in_end_time')->value('value');
        $coTime = Setting::where('key', 'check_out_time')->value('value');

        [$ciSH, $ciSM] = explode(':', $ciStart);
        [$ciEH, $ciEM] = explode(':', $ciEnd);
        [$coH,  $coM] = explode(':', $coTime);

        $checkInStart = Carbon::parse($dateIn)->addHours((int) $ciSH)->addMinutes((int) $ciSM);
        $checkInEnd = Carbon::parse($dateIn)->addDay()->addHours((int) $ciEH)->addMinutes((int) $ciEM)->addSecond(59);
        $checkOutCutoff = Carbon::parse($dateIn)->addHours((int) $coH)->addMinutes((int) $coM);

        return Card::where(function ($q) use ($checkInStart, $checkInEnd, $checkOutCutoff) {
            $q->where('arrival_date', '>=', $checkInStart)
                ->where('arrival_date', '<', $checkInEnd)
                ->orWhere(function ($sub) use ($checkInStart, $checkOutCutoff) {
                    $sub->where('arrival_date', '<', $checkInStart)
                        ->where(function ($q2) use ($checkOutCutoff) {
                            $q2->where('departure_date', '>=', $checkOutCutoff)
                                ->orWhereNull('departure_date');
                        });
                });
        })->get();
    }

    public function getExpenditure(string $dateIn)
    {
        return Expense::where('expense_date', Carbon::parse($dateIn))->get();
    }

    // ─── PAGE DATA ───

    public function loadData(): void
    {
        if (blank($this->date)) {
            Notification::make()->title('No Date Selected')->body('Please select a date first.')->warning()->send();

            return;
        }

        $data = $this->showDailyCollection($this->date);
        $expenditure = $this->getExpenditure($this->date);

        if ($data->isEmpty() && $expenditure->isEmpty()) {
            Notification::make()->title('No Data')->body("No data exists for {$this->date}.")->warning()->send();

            return;
        }

        $this->setData();
    }

    public function setData()
    {
        $this->expenditures = $this->getExpensesGroupedByType($this->date);
        $this->totalExpenditure = $this->sumOfExpenditure($this->date);
        $this->dueBeforeToday = $this->dueBeforeThisMonth();
        $this->totalsRentByMonth = $this->balanceBeforeThisMonth();
        $this->data = $this->showDailyCollection($this->date);

        return $this->data;
    }

    public function getExpensesGroupedByType(string $dateIn)
    {
        return Expense::where('expense_date', Carbon::parse($dateIn))->get()
            ->groupBy('expense_type_id')
            ->map(fn ($group) => [
                'expense_type_name' => $group->first()->expenseType->name,
                'total_amount' => $group->sum('amount'),
            ]);
    }

    public function sumOfExpenditure(string $date): float
    {
        return (float) $this->getExpenditure($date)->sum('amount');
    }

    public function balanceBeforeThisMonth(): float
    {
        $start = Carbon::parse($this->date)->startOfMonth();
        $end = Carbon::parse($this->date);
        $total = 0.0;

        for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
            $str = $d->format('Y-m-d');
            $day = $this->showDailyCollection($str);
            $total += $day->sum(fn ($i) => ($i['cash_collected'] ?? 0) + ($i['adv_adjust'] ?? 0) + ($i['due_collection'] ?? 0));
            $total -= $this->sumOfExpenditure($str);
        }

        return $total;
    }

    public function dueBeforeThisMonth(): float
    {
        $start = Carbon::parse($this->date)->startOfMonth();
        $end = Carbon::parse($this->date);
        $total = 0.0;

        for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
            $total += $this->showDailyCollection($d->format('Y-m-d'))->sum(fn ($i) => $i['due'] ?? 0);
        }

        return $total;
    }

    // ─── ACTIONS ───

    public function openViewModal(string $cardNo): void
    {
        $card = Card::where('card_no', $cardNo)->firstOrFail();
        $this->card = (new DailyCollectionCalculator($card))->calculate();
        $this->selectedCardNo = $cardNo;
        $this->dispatch('open-modal', id: 'view-modal');
    }

    public function exportToExcel()
    {
        if (empty($this->data) || (is_object($this->data) && $this->data->isEmpty())) {
            Notification::make()->title('No Data Found')->body('Search with a different date.')->warning()->send();

            return;
        }

        $this->setData();
        $format = Carbon::parse($this->date)->format('d-m-y');

        return Excel::download(new DailyRoomSheetExport($this->data, $this->date), "daily-room-sheet-{$format}.xlsx");
    }

    public function savePDF(): string
    {
        $this->date = Carbon::parse($this->date)->format('Y-m-d');
        $filePath = "{$this->date}-invoice.pdf";

        Pdf::view('pdf.invoice', [
            'data' => $this->data,
            'expenditures' => $this->expenditures,
            'totalsRentByMonth' => $this->totalsRentByMonth,
            'totalExpenditure' => $this->totalExpenditure,
            'dueBeforeToday' => $this->dueBeforeToday,
            'date' => $this->date,
        ])->save(storage_path("app/{$filePath}"));

        return $filePath;
    }
}
