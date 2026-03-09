<?php

namespace App\Exports;

use App\Models\Card;
use App\Models\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CardPaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $date;

    protected array $paymentTypeIds;

    public function __construct($date, array $paymentTypeIds = [])
    {
        $this->date = $date;
        $this->paymentTypeIds = $paymentTypeIds;
    }

    public function collection()
    {
        $start = Carbon::parse($this->date)->setTime(11, 0, 0);
        $end = Carbon::parse($this->date)->addDay()->setTime(10, 59, 59);

        return Payment::with(['paymentable', 'paymentType', 'user'])
            ->where('paymentable_type', Card::class)
            ->whereBetween('created_at', [$start, $end])
            ->when(! empty($this->paymentTypeIds), function ($q) {
                $q->whereIn('payment_type_id', $this->paymentTypeIds);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Received Time',
            'Card No',
            'Reservation No',
            'Payment Type',
            'Transaction No',
            'Amount',
            'Received By',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->created_at->format('d-m-Y h:i A'),
            $payment->paymentable?->card_no,
            $payment->paymentable?->reservation_id,
            $payment->paymentType?->name,
            $payment->tnx,
            $payment->amount,
            $payment->user?->name,
        ];
    }
}
