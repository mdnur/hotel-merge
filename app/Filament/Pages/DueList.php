<?php

namespace App\Filament\Pages;

use App\Exports\DueListExport;
use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\PaymentType;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class DueList extends Page
{
    // protected static BackedEnum|string|null $navigationIcon = Heroicon::DocumentText;

    protected string $view = 'filament.pages.due-list';

    public $date;

    public $items;

    public $selectedCardNo;

    public $amount;

    public $payment_type_id;

    public $tnx;

    public $paymentTypes;

    public $payments;

    public $card;

    public function mount(): void
    {
        $this->date = Carbon::now()->subHours(11)->format('Y-m-d');
        $this->paymentTypes = PaymentType::all();
        $this->loadDueList();
    }

    public function loadDueList(): void
    {
        // Re-use the DailyRoomSheet page logic to get daily collection data
        $sheet = new DailyRoomSheet;
        $this->items = $sheet->showDailyCollection($this->date);
    }

    public function refreshData(): void
    {
        $this->paymentTypes = PaymentType::all();
        $this->loadDueList();
    }

    public function updatedDate(): void
    {
        $this->loadDueList();
    }

    public function openPaymentModal(string $cardNo, float $totalDue): void
    {
        $this->selectedCardNo = $cardNo;
        $this->amount = $totalDue;
        $this->dispatch('open-modal', id: 'receive-payment-modal');
    }

    public function savePayment(): void
    {
        $card = Card::where('card_no', $this->selectedCardNo)->firstOrFail();

        $card->payments()->create([
            'amount' => $this->amount,
            'payment_type_id' => $this->payment_type_id,
            'tnx' => $this->tnx,
            'user_id' => auth()->id(),
        ]);

        Notification::make()->title('Payment Successful')->success()->send();

        $this->dispatch('close-modal', id: 'receive-payment-modal');
        $this->reset(['amount', 'payment_type_id', 'tnx']);
        $this->loadDueList();
    }

    public function openViewModal(string $cardNo): void
    {
        $card = Card::where('card_no', $cardNo)->firstOrFail();
        $this->card = (new DailyCollectionCalculator($card))->calculate();
        $this->selectedCardNo = $cardNo;
        $this->dispatch('open-modal', id: 'view-modal');
    }

    public function openPaymentHistoryModal(string $cardNo): void
    {
        $this->payments = Card::where('card_no', $cardNo)->firstOrFail()->payments;
        $this->dispatch('open-modal', id: 'payment-history-modal');
    }

    public function exportToExcel()
    {
        if (! $this->items || $this->items->isEmpty()) {
            Notification::make()->title('No Data Found')->body('Try a different date.')->warning()->send();

            return;
        }

        $format = Carbon::parse($this->date)->format('d-m-y');

        return Excel::download(new DueListExport($this->items, $this->date), "due-list-{$format}.xlsx");
    }

    public function getCardEditUrl(string $cardNo): string
    {
        $id = \App\Models\Card::where('card_no', $cardNo)->first()?->id;

        return $id ? route('filament.admin.resources.cards.edit', ['record' => $id]) : '#';
    }
}
