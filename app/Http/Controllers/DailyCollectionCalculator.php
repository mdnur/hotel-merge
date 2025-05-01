<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Room;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyCollectionCalculator
{
    protected $card;

    protected $processedPayments = [];

    public function __construct($card)
    {
        $this->card = $card;
    }

    public function getTotalRent()
    {
        $totalDailyRent = collect($this->calculate())->sum('daily_rent');

        return $totalDailyRent;
    }

    public function getTotalDue()
    {
        $totalDue = collect($this->calculate())->sum('due');

        return $totalDue;
    }

    public function calculate()
    {
        $dailyRows = $this->generateDailyRows();
        if (empty($dailyRows)) {
            return collect([
                [
                    'specific_date' => null,
                    'reservation_no' => null,
                    'room_no' => null,
                    'daily_rent' => null,
                    'cash_collected' => null,
                    'adv_adjust' => null,
                    'due_collection' => null,
                    'due' => null,
                ],
            ]);
        }

        $dailyUsage = collect($dailyRows)->sortBy(['card_id', 'specific_date']);

        return $this->processDailyUsage($dailyUsage);
    }

    protected function generateDailyRows()
    {
        $dailyRows = [];
        foreach ($this->card->cardRooms as $room) {
            $checkIn = Carbon::parse($room->check_in);
            $checkOut = Carbon::parse($room->check_out);

            if (! $checkIn || ! $checkOut) {
                continue;
            }

            foreach ($this->generateDailySlices($checkIn, $checkOut) as $dayDate) {
                $dailyRows[] = [
                    'card_id' => $this->card->id,
                    'specific_date' => $dayDate,
                    'reservation_no' => $this->card->reservation_no ?? null,
                    'room_no' => $room->room_id,
                    'daily_rent' => $room->rent,
                ];
            }
        }

        return $dailyRows;
    }

    protected function generateDailySlices($checkIn, $checkOut)
    {

        $check_in_start_time = Setting::where('key', '=', 'check_in_start_time')->get()->first()->value;
        $filterCheckInStartTime = explode(':', $check_in_start_time);

        $endDate = $checkOut->copy();
        $dayLabel = $checkIn->isBefore($checkIn->copy()->startOfDay()->addHours((int) $filterCheckInStartTime[0])->addMinute((int) $filterCheckInStartTime[1]))
            ? $checkIn->copy()->subDay()->startOfDay()->addHours((int) $filterCheckInStartTime[0])->addMinute((int) $filterCheckInStartTime[1]) // Adjust to the previous day's 5:00 AM
            : $checkIn->copy()->startOfDay()->addHours((int) $filterCheckInStartTime[0])->addMinute((int) $filterCheckInStartTime[1]);          // Use 5:00 AM of the current day

        // Define the cutoff time for checkout (11:00 AM)

        $check_out_time = Setting::where('key', '=', 'check_out_time')->get()->first()->value;
        $filterCheckOutTime = explode(':', $check_out_time);

        $checkoutCutoff = $endDate->copy()->startOfDay()->addHours((int) $filterCheckOutTime[0])->addMinute((int) $filterCheckOutTime[1]);

        // If checkout is before 11:00 AM, exclude the checkout day
        if ($endDate->lt($checkoutCutoff)) {
            $endDate = $endDate->copy()->subDay()->endOfDay(); // Set end date to the previous day
        }

        // Generate daily slices
        while ($dayLabel < $endDate) {
            yield $dayLabel->copy()->toDateString();
            $dayLabel->addDay();
        }
    }

    // protected function processDailyUsage(Collection $dailyUsage)
    // {
    //     $cardStatus = [
    //         'due' => 0.0,
    //         'advance' => 0.0,
    //         'payments_index' => 0,
    //     ];

    //     $results = [];
    //     $payments = $this->card->payments->sortBy('created_at');

    //     // Group daily usage by specific_date
    //     $groupedUsage = $dailyUsage->groupBy('specific_date');

    //     foreach ($groupedUsage as $date => $usages) {
    //         $specificDate = Carbon::parse($date);
    //         $dailyRent = $usages->sum('daily_rent');
    //         // dd($usages);

    //         $dueBefore = $cardStatus['due'];
    //         $advBefore = $cardStatus['advance'];
    //         $payIndex = $cardStatus['payments_index'];

    //         $roomNos = $usages->pluck('room_no')->unique()->implode(',');
    //         $roomNos = $usages->pluck('room_no')
    //             ->map(function ($roomNo) {
    //                 // dd(Room::find($roomNo))
    //                 return Room::find($roomNo)->room_no;
    //             })
    //             ->implode(',');

    //         // Calculate total payments for the day
    //         $totalPayToday = $this->calculatePaymentsForDay($specificDate, $cardStatus['payments_index'], $payments, $cardStatus);

    //         $cashCollected = 0.0;
    //         $dueCollection = 0.0;
    //         $advAdjust = 0.0;

    //         if ($advBefore > 0) {
    //             $advAdjust = min($advBefore, $dailyRent);
    //             $dailyRent -= $advAdjust;
    //             $cardStatus['advance'] -= $advAdjust;
    //         }

    //         $newDue = $dueBefore + $dailyRent;

    //         if ($totalPayToday > 0) {
    //             if ($dueBefore > 0) {
    //                 $dueCollection = min($dueBefore, $totalPayToday);
    //                 $totalPayToday -= $dueCollection;
    //                 $newDue -= $dueCollection;
    //             }

    //             if ($totalPayToday > 0) {
    //                 $cashCollected = min($dailyRent, $totalPayToday);
    //                 $totalPayToday -= $cashCollected;
    //                 $newDue -= $cashCollected;
    //             }

    //             if ($totalPayToday > 0) {
    //                 $cardStatus['advance'] += $totalPayToday;
    //             }
    //         }

    //         $cardStatus['due'] = max(0, $newDue);

    //         // Add result for the date
    //         $results[] = [
    //             'specific_date' => $specificDate,
    //             'card_no' => Card::findOrFail($usages->first()['card_id'])->card_no,
    //             'room_no' => $roomNos,
    //             'daily_rent' => $usages->sum('daily_rent'), // Total rent including advance adjustment
    //             'cash_collected' => $cashCollected,
    //             'adv_adjust' => $advAdjust,
    //             'due_collection' => $dueCollection,
    //             'total_due' => $cardStatus['due'],
    //             'due' => $usages->sum('daily_rent') - $advAdjust - $cashCollected,
    //             'advance' => $cardStatus['advance'],
    //         ];
    //     }

    //     return collect($results);
    // }

    // protected function calculatePaymentsForDay($specificDate, &$payIndex, $payments, &$cardStatus)
    // {
    //     $totalPayToday = 0.0;

    //     // Define billing period
    //     $billingStart = $specificDate->copy()->setTime(5, 0); // Start at 5:00 AM

    //     // $billingStart = $specificDate->copy()->setTime(5, 0); // Start at 5:00 AM
    //     $billingEnd = $billingStart->copy()->addHours(30);  // End 30 hours later (next day at 11:00 AM)

    //     // $billing_start = Setting::where('key', '=', 'billing_start')->get()->first()->value;
    //     // $filterBillingStart = explode(':', $billing_start);

    //     // $billingStart = $specificDate->copy()->setTime((int) $filterBillingStart[0], (int) $filterBillingStart[1]); // Start at 5:00 AM

    //     // $billing_end = Setting::where('key', '=', 'billing_end')->get()->first()->value;
    //     // $filterBillingEnd = explode(':', $billing_end);
    //     // $billingEnd = $specificDate->copy()->setTime(0, 00)->addDay(1)->addHour((int) $filterBillingEnd[0])->addMinute((int) $filterBillingEnd[1]);

    //     // Debugging: Print billing period
    //     // echo "Billing Start: " . $billingStart . "<br>";
    //     // echo "Billing End: " . $billingEnd . "<br>";

    //     // Iterate through payments
    //     foreach ($payments as $payment) {
    //         $paymentCreatedAt = Carbon::parse($payment->created_at);

    //         // Debugging: Print payment creation time
    //         // echo "Payment Created At: " . $paymentCreatedAt . "<br>";

    //         // Check if payment falls within billing period
    //         if ($paymentCreatedAt->between($billingStart, $billingEnd)) {
    //             $totalPayToday += $payment->amount;
    //         }
    //     }

    //     return $totalPayToday;
    // }
    protected function processDailyUsage(Collection $dailyUsage)
    {
        $cardStatus = [
            'due' => 0.0,
            'advance' => 0.0,
            'payments_index' => 0,
        ];

        $results = [];
        $payments = $this->card->payments->sortBy('created_at');
        $this->processedPayments = array_fill(0, count($payments), false);

        // Group daily usage by specific_date
        $groupedUsage = $dailyUsage->groupBy('specific_date');

        foreach ($groupedUsage as $date => $usages) {
            $specificDate = Carbon::parse($date);
            $dailyRent = $usages->sum('daily_rent');

            $dueBefore = $cardStatus['due'];
            $advBefore = $cardStatus['advance'];

            $roomNos = $usages->pluck('room_no')
                ->map(function ($roomNo) {
                    return Room::find($roomNo)->room_no;
                })
                ->implode(',');

            // Calculate total payments for the day
            $totalPayToday = $this->calculatePaymentsForDay($specificDate, $payments, $cardStatus);

            // ... [rest of your payment processing logic remains the same] ...
            $cashCollected = 0.0;
            $dueCollection = 0.0;
            $advAdjust = 0.0;

            if ($advBefore > 0) {
                $advAdjust = min($advBefore, $dailyRent);
                $dailyRent -= $advAdjust;
                $cardStatus['advance'] -= $advAdjust;
            }

            $newDue = $dueBefore + $dailyRent;

            if ($totalPayToday > 0) {
                if ($dueBefore > 0) {
                    $dueCollection = min($dueBefore, $totalPayToday);
                    $totalPayToday -= $dueCollection;
                    $newDue -= $dueCollection;
                }

                if ($totalPayToday > 0) {
                    $cashCollected = min($dailyRent, $totalPayToday);
                    $totalPayToday -= $cashCollected;
                    $newDue -= $cashCollected;
                }

                if ($totalPayToday > 0) {
                    $cardStatus['advance'] += $totalPayToday;
                }
            }

            $cardStatus['due'] = max(0, $newDue);

            // Add result for the date
            $results[] = [
                'specific_date' => $specificDate,
                'card_no' => Card::findOrFail($usages->first()['card_id'])->card_no,
                'room_no' => $roomNos,
                'daily_rent' => $usages->sum('daily_rent'), // Total rent including advance adjustment
                'cash_collected' => $cashCollected,
                'adv_adjust' => $advAdjust,
                'due_collection' => $dueCollection,
                'total_due' => $cardStatus['due'],
                'due' => $usages->sum('daily_rent') - $advAdjust - $cashCollected,
                'advance' => $cardStatus['advance'],
            ];
        }

        return collect($results);
    }

    protected function calculatePaymentsForDay($specificDate, $payments, &$cardStatus)
    {
        $totalPayToday = 0.0;

        $billing_start = Setting::where('key', '=', 'billing_start')->get()->first()->value;
        $filterBillingStart = explode(':', $billing_start);

        $billingStart = $specificDate->copy()->setTime((int) $filterBillingStart[0], (int) $filterBillingStart[1]); // Start at 5:00 AM

        $billing_end = Setting::where('key', '=', 'billing_end')->get()->first()->value;
        $filterBillingEnd = explode(':', $billing_end);
        $billingEnd = $specificDate->copy()->setTime(0, 00)->addDay(1)->addHour((int) $filterBillingEnd[0])->addMinute((int) $filterBillingEnd[1]);

        foreach ($payments as $index => $payment) {
            // Skip already processed payments
            if ($this->processedPayments[$index]) {
                continue;
            }

            $paymentCreatedAt = Carbon::parse($payment->created_at);

            if ($paymentCreatedAt->between($billingStart, $billingEnd)) {
                $totalPayToday += $payment->amount;
                $this->processedPayments[$index] = true;
                $cardStatus['payments_index'] = $index + 1; // Track last processed index
            } elseif ($paymentCreatedAt->gte($billingEnd)) {
                // Payments are sorted chronologically, so we can break early
                break;
            }
        }

        return $totalPayToday;
    }
}
