<?php

namespace App\Livewire;

use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Component;

class DueList extends Component
{
    public $showModal = false;

    public $items = [];

    public $selectedCard;

    public $amount;

    public $payment_type_id;

    public $transactions_no;

    public $data = [];

    public $paymentTypes;

    public $payments;

    public $date;

    public $card;

    public function savePayment()
    {
        $card = Card::where('card_no', $this->selectedCard)->first();
        // dd($card->id);
        $this->data = [
            'card_id' => $card->id,
            'user_id' => auth()->user()->id,
            'amount' => $this->amount,
            'payment_type_id' => $this->payment_type_id ?? null,
            'transactions_no' => $this->transactions_no,
        ];
        // dd($this->payment_type_id);
        // dd($this->data);
        // dd($this->data);
        Payment::create($this->data);
        // Optionally show notification
        Notification::make()
            ->title('Payment Successful')
            ->success()
            ->send();

        // $this->emit('refreshTable'); // Refresh the table

        $this->dispatch('close-modal', id: 'receive-payment-modal');
        $this->reset(['amount', 'payment_type_id', 'transactions_no']);
        $this->render(); // Livewire component refresh
    }

    public function render()
    {
        $this->loadDueList();

        $this->paymentTypes = \App\Models\PaymentType::all();

        return view('livewire.due-list');
    }

    public function mount()
    {
        $this->loadDueList();
    }

    public function openPaymentModal($cardNo)
    {

        $this->dispatch('open-modal', id: 'receive-payment-modal');
        // dd($cardNo);
        $this->selectedCard = $cardNo;

        // $this->selectedCard = $cardNo;
        // $this->paymentAmount = null;
        // $this->showModal = true;
    }

    public function loadDueList()
    {
        $this->date = $this->date ?? Carbon::now()->subHour(11)->format('Y-m-d');
        $dailyRoomSheet = new DailyRoomSheet;
        // $this->items = $dailyRoomSheet->showDailyCollection(Carbon::now(11->format('Y-m-d'));
        $this->items = $dailyRoomSheet->showDailyCollection($this->date);
        // dd($this->items)
    }

    public function refreshData()
    {
        // // dd($date);
        // $this->items = [];
        // $dailyRoomSheet = new DailyRoomSheet;
        // $this->items = $dailyRoomSheet->showDailyCollection($this->date);
        // dd($this->items);
        $this->render();
        dd($this->items);
        // $this->render();
    }

    public function refreshData1()
    {
        // dd($this->date);
        $this->items = [];
        $dailyRoomSheet = new DailyRoomSheet;
        $this->items = $dailyRoomSheet->showDailyCollection($this->date);
        // dd($this->items);
        // dd($this->items);

        return $this->render();
    }

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
}
