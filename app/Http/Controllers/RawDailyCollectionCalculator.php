<?php

class DailyCollectionCalculator
{
    private $cards;
    private $cardRooms;
    private $cardPayments;

    public function __construct($cards, $cardRooms, $cardPayments)
    {
        $this->cards = $cards;
        $this->cardRooms = $cardRooms;
        $this->cardPayments = $cardPayments;
    }

    private function generateDailySlices($checkIn, $checkOut)
    {
        if ($checkIn === null || $checkOut === null) {
            return [];
        }
        if ($checkIn >= $checkOut) {
            return [];
        }

        $baseDate = $checkIn->format('Y-m-d');
        $fiveAMThisDate = new DateTime($baseDate . ' 05:00:00');
        $dayLabel = $checkIn < $fiveAMThisDate ? $fiveAMThisDate->modify('-1 day') : new DateTime($baseDate);

        $slices = [];
        $firstIteration = true;

        while (true) {
            $dayStart = new DateTime($dayLabel->format('Y-m-d') . ' 05:00:00');
            $dayEnd = (clone $dayStart)->modify('+24 hours -1 second');

            $occupantPresent = $checkIn < $dayEnd && $checkOut > $dayStart;

            if ($firstIteration && $occupantPresent) {
                $slices[] = clone $dayLabel;
                $firstIteration = false;
            }

            $nextDay11AM = (clone $dayLabel)->modify('+1 day 11:00:00');
            if ($checkOut < $nextDay11AM) {
                break;
            }

            $dayLabel->modify('+1 day');
            $slices[] = clone $dayLabel;

            $dayStart = new DateTime($dayLabel->format('Y-m-d') . ' 05:00:00');
            if ($dayStart >= $checkOut) {
                break;
            }
        }

        return $slices;
    }

    public function calculateDailyCollection($startDate = null, $endDate = null)
    {
        $dailyRows = [];

        foreach ($this->cardRooms as $room) {
            $checkIn = new DateTime($room['check_in']);
            $checkOut = new DateTime($room['check_out']);

            foreach ($this->generateDailySlices($checkIn, $checkOut) as $specificDate) {
                $dailyRows[] = [
                    'card_id' => $room['card_id'],
                    'specific_date' => $specificDate->format('Y-m-d'),
                    'reservation_no' => $this->cards[0]['reservation_no'],
                    'room_no' => $room['room_no'],
                    'daily_rent' => $room['rent']
                ];
            }
        }

        if (empty($dailyRows)) {
            return [];
        }

        usort($dailyRows, function ($a, $b) {
            return $a['specific_date'] <=> $b['specific_date'] ?: $a['room_no'] <=> $b['room_no'];
        });

        $paymentDict = [];
        foreach ($this->cardPayments as $payment) {
            $cardId = $payment['card_id'];
            if (!isset($paymentDict[$cardId])) {
                $paymentDict[$cardId] = [];
            }
            $paymentDict[$cardId][] = [$payment['created_at'], $payment['amount']];
        }

        $results = [];
        $cardStatus = [];

        foreach ($dailyRows as $row) {
            $cardId = $row['card_id'];
            $dayDate = $row['specific_date'];
            $dayRent = $row['daily_rent'];

            if (!isset($cardStatus[$cardId])) {
                $cardStatus[$cardId] = ['due' => 0.0, 'advance' => 0.0, 'payments_index' => 0];
            }

            $dueBefore = $cardStatus[$cardId]['due'];
            $advBefore = $cardStatus[$cardId]['advance'];
            $payIndex = $cardStatus[$cardId]['payments_index'];

            $totalPayToday = 0.0;
            if (isset($paymentDict[$cardId])) {
                $paymentsList = $paymentDict[$cardId];
                while ($payIndex < count($paymentsList)) {
                    [$paymentDate, $paymentAmount] = $paymentsList[$payIndex];
                    $billingStart = new DateTime($dayDate . ' 05:00:00');
                    $billingEnd = (clone $billingStart)->modify('+30 hours');

                    if ($billingStart <= new DateTime($paymentDate) && new DateTime($paymentDate) < $billingEnd) {
                        $totalPayToday += $paymentAmount;
                        $payIndex++;
                    } else {
                        break;
                    }
                }
            }
            $cardStatus[$cardId]['payments_index'] = $payIndex;

            $cashCollected = 0.0;
            $dueCollection = 0.0;
            $advAdjust = 0.0;
            $rentCoveredByAdvance = 0.0;

            if ($advBefore > 0) {
                $advAdjust = $advBefore;
                if ($advBefore >= $dayRent) {
                    $rentCoveredByAdvance = $dayRent;
                    $cardStatus[$cardId]['advance'] -= $dayRent;
                    $dayRent = 0.0;
                } else {
                    $rentCoveredByAdvance = $advBefore;
                    $cardStatus[$cardId]['advance'] = 0.0;
                    $dayRent -= $advBefore;
                }
            }

            $newDue = $dueBefore + $dayRent;

            if ($totalPayToday > 0) {
                if ($dueBefore > 0) {
                    $dueCollection = min($dueBefore, $totalPayToday);
                    $totalPayToday -= $dueCollection;
                    $newDue -= $dueCollection;
                }

                if ($totalPayToday > 0) {
                    $cashCollected = min($dayRent, $totalPayToday);
                    $totalPayToday -= $cashCollected;
                    $newDue -= $cashCollected;
                }

                if ($totalPayToday > 0) {
                    $cardStatus[$cardId]['advance'] += $totalPayToday;
                    $advAdjust = 0;
                }
            }

            $cardStatus[$cardId]['due'] = max(0, $newDue);

            $results[] = [
                'specific_date' => $dayDate,
                'reservation_no' => $row['reservation_no'],
                'room_no' => $row['room_no'],
                'daily_rent' => $row['daily_rent'],
                'cash_collected' => $cashCollected,
                'adv_adjust' => $advAdjust,
                'due_collection' => $dueCollection,
                'total_due' => $cardStatus[$cardId]['due'],
                'due' => $row['daily_rent']-$advAdjust-$cashCollected,
                'advance' => $cardStatus[$cardId]['advance']
            ];
        }

        // "total_due": card_status[c_id]["due"],
        // "due": row.daily_rent - adv_adjust -cash_collected,

        if ($startDate !== null) {
            $results = array_filter($results, function ($row) use ($startDate) {
                return $row['specific_date'] >= $startDate;
            });
        }

        if ($endDate !== null) {
            $results = array_filter($results, function ($row) use ($endDate) {
                return $row['specific_date'] <= $endDate;
            });
        }

        usort($results, function ($a, $b) {
            return $a['specific_date'] <=> $b['specific_date'] ?: $a['reservation_no'] <=> $b['reservation_no'] ?: $a['room_no'] <=> $b['room_no'];
        });

        return $results;
    }
}

// Input data
$cards = [
    ['id' => 1, 'reservation_no' => 1001, 'arrival_date' => '2025-01-13', 'departure_date' => '2025-01-16']
];

$cardRooms = [
    ['id' => 1, 'room_no' => 105, 'check_in' => '2025-01-13 4:00', 'check_out' => '2025-01-14 10:50', 'card_id' => 1, 'rent' => 2000, 'created_at' => '2025-01-13'],
    ['id' => 1, 'room_no' => 108, 'check_in' => '2025-01-13 4:00', 'check_out' => '2025-01-14 10:50', 'card_id' => 1, 'rent' => 2000, 'created_at' => '2025-01-13'],
    ['id' => 2, 'room_no' => 108, 'check_in' => '2025-01-14 11:00', 'check_out' => '2025-01-16 12:00', 'card_id' => 1, 'rent' => 1800, 'created_at' => '2025-01-14']
];

$cardPayments = [
    ['id' => 1, 'amount' => 2000, 'created_at' => '2025-01-13 06:00', 'card_id' => 1],
    ['id' => 2, 'amount' => 3600, 'created_at' => '2025-01-16 11:00', 'card_id' => 1],
    ['id' => 2, 'amount' => 3800, 'created_at' => '2025-01-16 11:00', 'card_id' => 1]
];

// Calculate daily collection
$calculator = new DailyCollectionCalculator($cards, $cardRooms, $cardPayments);
$results = $calculator->calculateDailyCollection();

echo "Final Daily Collection Data:\n";
print_r($results);

?>
