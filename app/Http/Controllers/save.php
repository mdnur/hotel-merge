<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyCollectionCalculator1
{
    protected $card;

    public function __construct($card)
    {
        $this->card = $card;
    }

    public function calculate()
    {
        $dailyRows = $this->generateDailyRows();
        if (empty($dailyRows)) {
            return collect([
                [
                    "specific_date" => null,
                    "reservation_no" => null,
                    "room_no" => null,
                    "daily_rent" => null,
                    "cash_collected" => null,
                    "adv_adjust" => null,
                    "due_collection" => null,
                    "due" => null,
                ]
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

            if (!$checkIn || !$checkOut) {
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

    // protected function generateDailySlices($checkIn, $checkOut)
    // {
    //     $dayLabel = $checkIn->copy()->startOfDay()->addHours(5);
    //     $endDate = $checkOut->copy();

    //     while ($dayLabel < $endDate) {
    //         yield $dayLabel->copy()->toDateString();
    //         $dayLabel->addDay();
    //     }
    // }


    protected function generateDailySlices($checkIn, $checkOut)
    {
        // $dayLabel = $checkIn->copy()->startOfDay()->addHours(5);
        $endDate = $checkOut->copy();
        $dayLabel = $checkIn->isBefore($checkIn->copy()->startOfDay()->addHours(5))
            ? $checkIn->copy()->subDay()->startOfDay()->addHours(5) // Adjust to the previous day's 5:00 AM
            : $checkIn->copy()->startOfDay()->addHours(5); // Use 5:00 AM of the current day


        while ($dayLabel < $endDate) {
            yield $dayLabel->copy()->toDateString();
            $dayLabel->addDay();
        }
    }

    // private function generateDailySlices(Carbon $checkIn, Carbon $checkOut)
    // {
    //     if ($checkIn === null || $checkOut === null || $checkIn->gte($checkOut)) {
    //         return [];
    //     }

    //     $baseDate = $checkIn->format('Y-m-d');
    //     $fiveAMThisDate = Carbon::parse($baseDate . ' 05:00:00');
    //     $dayLabel = $checkIn->lt($fiveAMThisDate) ? $fiveAMThisDate->subDay() : Carbon::parse($baseDate);

    //     $slices = [];
    //     $firstIteration = true;

    //     while (true) {
    //         $dayStart = $dayLabel->copy()->setTime(5, 0, 0);
    //         $dayEnd = $dayStart->copy()->addDay()->subSecond();

    //         $occupantPresent = $checkIn->lt($dayEnd) && $checkOut->gt($dayStart);

    //         if ($firstIteration && $occupantPresent) {
    //             $slices[] = $dayLabel->copy();
    //             $firstIteration = false;
    //         }

    //         $nextDay11AM = $dayLabel->copy()->addDay()->setTime(11, 0, 0);
    //         if ($checkOut->lt($nextDay11AM)) {
    //             break;
    //         }

    //         $dayLabel->addDay();
    //         $slices[] = $dayLabel->copy();

    //         $dayStart = $dayLabel->copy()->setTime(5, 0, 0);
    //         if ($dayStart->gte($checkOut)) {
    //             break;
    //         }
    //     }

    //     return $slices;
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

        foreach ($dailyUsage as $usage) {
            $specificDate = Carbon::parse($usage['specific_date']);
            $dailyRent = $usage['daily_rent'];

            $dueBefore = $cardStatus['due'];
            $advBefore = $cardStatus['advance'];
            $payIndex = $cardStatus['payments_index'];

            $totalPayToday = $this->calculatePaymentsForDay($specificDate, $payIndex, $payments, $cardStatus);
            // dd($totalPayToday);
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

            $results[] = [
                'specific_date' => $specificDate,
                'reservation_no' => $usage['reservation_no'],
                'room_no' => $usage['room_no'],
                'daily_rent' => $usage['daily_rent'],
                'cash_collected' => $cashCollected,
                'adv_adjust' => $advAdjust,
                'due_collection' => $dueCollection,
                'total_due' => $cardStatus['due'],
                'due' => $usage['daily_rent'] - $advAdjust - $cashCollected,
                'advance' => $cardStatus['advance'],
            ];
        }

        return collect($results);
    }

    // protected function calculatePaymentsForDay($specificDate, &$payIndex, $payments, &$cardStatus)
    // {
    //     $totalPayToday = 0.0;
    //     // Define billing period
    //     $billingStart = $specificDate->copy()->setTime(5, 0);
    //     $billingEnd = $billingStart->copy()->addHours(30);

    //     // dd($payments);
    //     foreach ($payments as $payment) {
    //         $paymentCreatedAt = Carbon::parse($payment->created_at);
    //         // dd($billingStart, $billingEnd, $paymentCreatedAt);

    //         // dd($payment->amount);

    //         if ($paymentCreatedAt->between($billingStart, $billingEnd)) {
    //             $totalPayToday += $payment->amount;
    //         } else {
    //             break; // Exit loop if payment is outside the billing period
    //         }
    //     }
    //     // dd($totalPayToday);
    //     // Iterate through payments
    //     // while ($payIndex < $payments->count()) {
    //     //     $payment = $payments[$payIndex];
    //     //     $paymentCreatedAt = Carbon::parse($payment->created_at);

    //     //     // Check if payment falls within billing period
    //     //     if ($paymentCreatedAt->between($billingStart, $billingEnd)) {
    //     //         $totalPayToday += $payment->amount;
    //     //         $payIndex++; // Move to the next payment
    //     //     } else {
    //     //         break; // Exit loop if payment is outside the billing period
    //     //     }

    //     //     // dd($payIndex);

    //     //     return $totalPayToday;
    //     // }

    //     return $totalPayToday;
    // }


    protected function calculatePaymentsForDay($specificDate, &$payIndex, $payments, &$cardStatus)
    {
        $totalPayToday = 0.0;

        // Define billing period
        $billingStart = $specificDate->copy()->setTime(5, 0); // Start at 5:00 AM
        $billingEnd = $billingStart->copy()->addHours(30); // End 30 hours later (next day at 11:00 AM)

        // // Debugging: Print billing period
        echo "Billing Start: " . $billingStart . "<br>";
        echo "Billing End: " . $billingEnd . "<br>";

        // Iterate through payments
        foreach ($payments as $payment) {
            $paymentCreatedAt = Carbon::parse($payment->created_at);

            // Debugging: Print payment creation time
            echo "Payment Created At: " . $paymentCreatedAt . "<br>";

            // Check if payment falls within billing period
            if ($paymentCreatedAt->between($billingStart, $billingEnd)) {
                $totalPayToday += $payment->amount;
            }
        }

        return $totalPayToday;
    }

}
