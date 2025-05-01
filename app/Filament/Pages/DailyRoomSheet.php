<?php

namespace App\Filament\Pages;

use App\Exports\DailyRoomSheetExport;
use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

class DailyRoomSheet extends Page
{
    protected static string $view = 'filament.pages.daily-room-sheet';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public $date;

    public $data = [];

    public $totalsRentByMonth = 0;

    public $totalExpenditure = 0;

    public $expenditures = [];

    public $dueBeforeToday = 0;

    public $card;

    // public static function canViewAny(): bool
    // {
    //     return false;

    //     return auth()->user()->can('');
    // }

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin() or auth()->user()->can('view DailyRoomSheet');
        // return auth()->user()?->can('view daily room sheet') ?? false;
    }

    protected function getFormSchema(): array
    {
        return [

            Section::make('Select Date')
                ->description('Select a date to generate the report')
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->default(now()->format('Y-m-d'))
                        ->maxDate(now())
                        ->native(false)
                        ->reactive(),
                ])->columns(2)
                ->headerActions([])

                ->footerActions([
                    Action::make('Generate Report')
                        ->color('success')
                        ->action(function () {
                            $this->loadData();
                        })->disabled(fn () => blank($this->date)),

                    Action::make('Download')
                        ->url(fn () => $this->date ? route('download.invoice', ['date' => Carbon::parse($this->date)->format('d-m-Y')]) : null)
                        ->openUrlInNewTab()
                        ->disabled(fn () => blank($this->date)), // disables button if no date

                    // Action::make('save')
                    //     ->action(function () {
                    //         $this->savePDF();
                    //     })->disabled(fn () => blank($this->date))
                    //     ->disabled(fn () => blank($this->date)), // disables button if no date
                ]),
        ];
    }

    public function loadData()
    {
        if ($this->date == null) {
            Notification::make()
                ->title('NO Date Selected')
                ->body('First Select a date')
                ->warning()
                ->send();

            return;
        }
        // dd($this->setData()->first());
        // Check if data exists for the selected date
        if ($this->setData()->first() == null and $this->expenditure()->first() == null) {
            Notification::make()
                ->title('No Data Exists')
                ->body("No data exists for the {$this->date}")
                ->warning()
                ->send();

            return;
        }
        $this->setData();
    }

    public function balanceBeforeOFThisMonth()
    {

        $daily = new \App\Livewire\DailyRoomSheet;
        // dd($daily->expenditure($this->date));

        $startDate = Carbon::parse($this->date)->startOfMonth();
        $endDate = Carbon::parse($this->date);

        // Initialize monthly totals
        $monthlyTotals = [
            'total_room_rent' => 0,
            'total_cash_collected' => 0,
            'total_adv_adjust' => 0,
            'total_due_collection' => 0,
            'total_due' => 0,
            'total_advance' => 0,
            'total_rooms' => 0,
        ];

        // Loop through each day of the month up to selected date
        $currentDate = $startDate->copy();
        $total_expenditure_by_month = 0;
        while ($currentDate < $endDate) {
            $dailyTotals = $daily->showDailyCollection($currentDate->format('Y-m-d'));
            $dailyTotals->each(function ($item, $key) use (&$monthlyTotals) {
                $monthlyTotals['total_room_rent'] += ($item['cash_collected'] ?? 0) + ($item['adv_adjust'] ?? 0) + ($item['due_collection'] ?? 0);
            });

            $total_expenditure_by_month += $this->sumOfExpenditure($currentDate->format('Y-m-d'));

            // Accumulate totals
            // if ($dailyTotals) {
            //     $monthlyTotals['total_room_rent'] += $dailyTotals['daily_rent'] ?? 0;
            //     $monthlyTotals['total_cash_collected'] += $dailyTotals['cash_collected'] ?? 0;
            //     $monthlyTotals['total_adv_adjust'] += $dailyTotals['adv_adjust'] ?? 0;
            //     $monthlyTotals['total_due_collection'] += $dailyTotals['due_collection'] ?? 0;
            //     $monthlyTotals['total_due'] += $dailyTotals['due'] ?? 0;
            //     $monthlyTotals['total_advance'] += $dailyTotals['advance'] ?? 0;
            //     $monthlyTotals['total_rooms'] += $dailyTotals['room_count'] ?? 0;
            // }

            $currentDate->addDay();
        }

        return $monthlyTotals['total_room_rent'] - $total_expenditure_by_month;
        // $this->totals = $monthlyTotals;
        // return $this->totals;
    }

    public function sumOfExpenditure($date)
    {
        $daily = new \App\Livewire\DailyRoomSheet;
        $expenditure = $daily->expenditure($date);

        $sum = 0;
        foreach ($expenditure as $item) {
            $sum += $item['amount'];
        }

        return $sum;
    }

    public function expenditure()
    {
        $daily = new \App\Livewire\DailyRoomSheet;
        $results = $daily->expenditure($this->date);

        // return $results;
        return $results->groupBy('expense_type_id')->map(function ($item, $key) {

            return $item->sum('amount');
        });
    }

    public function getExpensesGroupedByType($dateIn)
    {
        $date = Carbon::parse($dateIn);

        $expenses = Expense::where('expense_date', $date)->get()
            ->groupBy('expense_type_id')
            ->map(function ($group) {
                return [
                    'expense_type_name' => $group->first()->expenseType->name, // Assuming a relationship
                    'total_amount' => $group->sum('amount'),
                ];
            });

        return $expenses;
    }

    public function DueBeforeOFThisMonth()
    {
        $daily = new \App\Livewire\DailyRoomSheet;

        $startDate = Carbon::parse($this->date)->startOfMonth();
        $endDate = Carbon::parse($this->date);

        // Initialize monthly totals
        $monthlyTotals = [
            'total_room_rent' => 0,
            'total_cash_collected' => 0,
            'total_adv_adjust' => 0,
            'total_due_collection' => 0,
            'total_due' => 0,
            'total_advance' => 0,
            'total_rooms' => 0,
        ];

        $currentDate = $startDate->copy();
        $total_expenditure_by_month = 0;
        while ($currentDate < $endDate) {
            $dailyTotals = $daily->showDailyCollection($currentDate->format('Y-m-d'));
            $dailyTotals->each(function ($item, $key) use (&$monthlyTotals) {
                $monthlyTotals['total_due'] += ($item['due'] ?? 0);
            });

            $total_expenditure_by_month += $this->sumOfExpenditure($currentDate->format('Y-m-d'));

            $currentDate->addDay();
        }

        return $monthlyTotals['total_due'];
    }

    public function downloadData($date)
    {
        $this->date = Carbon::parse($date)->format('Y-m-d');

        if ($this->date == null) {
            Notification::make()
                ->title('NO Date Selected')
                ->body('First Select a date')
                ->warning()
                ->send();

            return null;
        }

        if ($this->setData()->first() == null && $this->expenditure()->first() == null) {
            Notification::make()
                ->title('No Data Exists')
                ->body("No data exists for the {$this->date}")
                ->warning()
                ->send();

            return null;
        }

        return $this->savePDF(); // now returns relative path used in Storage
    }

    public function setData()
    {
        $daily = new \App\Livewire\DailyRoomSheet;
        $this->expenditures = $this->getExpensesGroupedByType($this->date);
        $this->totalExpenditure = $this->sumOfExpenditure($this->date);
        $this->dueBeforeToday = $this->DueBeforeOFThisMonth();

        $this->totalsRentByMonth = $this->balanceBeforeOFThisMonth();
        $daily = new \App\Livewire\DailyRoomSheet;
        $this->data = $daily->showDailyCollection($this->date);

        return $this->data ?? null;
    }

    public function savePDF()
    {
        $this->date = Carbon::parse($this->date)->format('Y-m-d');
        $filePath = "{$this->date}-invoice.pdf";

        // Save directly into storage/app/
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

    public function downloadPDF()
    {
        return Storage::download("{$this->date}-invoice.pdf");
    }

    public function openViewModal($cardNo)
    {
        $card = Card::where('card_no', $cardNo)->first();
        // dd($card);
        $this->card = (new DailyCollectionCalculator($card))->calculate();
        // dd($this->card);

        $this->dispatch('open-modal', id: 'view-modal');
    }

    public function exportToExcel()
    {
        if (! $this->data) {
            Notification::make()
                ->title('NO Data Found')
                ->body('search with different date')
                ->warning()
                ->send();

            return;
        }
        $this->setData();
        // dd($this->data);
        $format = Carbon::parse($this->date)->format('d-m-y');

        return Excel::download(new DailyRoomSheetExport($this->data, $this->date), "daily-room-sheet-{$format}.xlsx");
    }
}
