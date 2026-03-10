<?php

namespace App\Filament\Pages;

use App\Exports\CardPaymentsExport;
use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentType;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class TransitionHistory extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::DocumentText;

    protected string $view = 'filament.pages.transition-history';

    public $payments;

    public $date;

    public $card;

    public $cardPayments = [];

    public $expenses = [];

    public $paymentTypeId = null;

    public $paymentTypes = [];

    public array $paymentTypeIds = [];

    public function getAllExpenseByDate($date)
    {
        return Expense::whereDate('expense_date', $date)->get();
    }

    // public function getAllCardPaymentByDate($date)
    // {
    //     $start = Carbon::parse($date)->setTime(11, 0, 0);
    //     $end = Carbon::parse($date)->addDay()->setTime(10, 59, 59);

    //     return Payment::where('paymentable_type', \App\Models\Card::class)
    //         ->whereBetween('created_at', [$start, $end])
    //         ->where('payment_type_id', '=', '1')
    //         ->get();
    // }

    public function getAllCardPaymentByDate($date)
    {
        $start = Carbon::parse($date)->setTime(11, 0, 0);
        $end = Carbon::parse($date)->addDay()->setTime(10, 59, 59);

        return Payment::with([
            'paymentable.cardRooms.room',
            'paymentType',
            'user',
        ])
            ->where('paymentable_type', \App\Models\Card::class)
            ->whereBetween('created_at', [$start, $end])
            ->when(! empty($this->paymentTypeIds), function ($q) {
                $q->whereIn('payment_type_id', $this->paymentTypeIds);
            })
            ->get();
    }

    // public function mount()
    // {
    //     $this->date = Carbon::now()->subDay(1)->toDateString();
    //     $this->loadData();
    // }

    public function mount()
    {
        $this->date = Carbon::now()->subDay(1)->toDateString();
        $this->paymentTypes = PaymentType::get();
        $this->loadData();
    }

    public function loadData()
    {
        $this->cardPayments = $this->getAllCardPaymentByDate($this->date);
        $this->expenses = $this->getAllExpenseByDate($this->date);
    }

    public function updatedDate()
    {
        $this->loadData();
    }

    // public function form(Schema $form): Schema
    // {
    //     return $form
    //         ->schema([
    //             DatePicker::make('date')
    //                 ->label('Select Date')
    //                 ->live()
    //                 ->native(false)
    //                 ->required(),
    //         ]);
    // }

    public function openViewModal($cardNo)
    {
        $card = Card::where('card_no', $cardNo)->first();
        // dd($card);
        $this->card = (new DailyCollectionCalculator($card))->calculate();
        // dd($this->card);

        $this->dispatch('open-modal', id: 'view-modal');
    }

    public function openPaymentHistoryModal($cardNo)
    {
        // dd($cardNo);
        $card = Card::where('card_no', $cardNo)->first()->payments;
        // dd($card);
        // Get all paymentTypes for this card
        // $this->payments = Payment::where('card_id', $card->id)->get();
        $this->payments = Card::where('card_no', $cardNo)->first()->payments;
        // dd($this->payments);

        $this->dispatch('open-modal', id: 'payment-history-modal');
    }

    public function updatedPaymentTypeId()
    {
        $this->loadData();
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            DatePicker::make('date')
                ->label('Select Date')
                ->live()
                ->native(false)
                ->required(),

            Select::make('paymentTypeIds')
                ->label('Payment Types')
                ->options(
                    $this->paymentTypes->pluck('name', 'id')
                )
                ->multiple()
                ->searchable()
                ->placeholder('All Payment Types')
                ->live(),
        ]);
    }

    public function updatedPaymentTypeIds()
    {
        $this->loadData();
    }

    public function exportExcel()
    {
        return Excel::download(
            new CardPaymentsExport($this->date, $this->paymentTypeIds),
            'card-payments-'.$this->date.'.xlsx'
        );
    }
}
