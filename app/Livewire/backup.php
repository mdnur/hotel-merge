<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Card;
use Livewire\Component;
use App\Http\Controllers\DailyCollectionCalculator;

class DailyRoomSheet extends Component
{
    public $date;

    public function save()
    {
        // $helper = new \App\Http\Controllers\Helper;
        // $this->date =
        return $this->render();
        // $data = $helper->checkRentPayment($dateIn);
        // return dd($data);
        // return dd($this->date);
    }
    public function render()
    {


        // $helper = new \App\Http\Controllers\Helper;
        // $dateIn = $this->date ?? '2024-05-19';
        echo $this->date;
        $dateIn = $this->date ?? now()->format('Y-m-d');;
        // $data = $helper->checkRentPayment($dateIn);
        $data = Card::with(['cardRooms', 'payments'])->findOrFail(1);

        echo $dateIn;
        // $data = $this->showDailyCollection($dateIn);
        $data = array();
        // dd($data);
        return view('livewire.daily-room-sheet', compact('data'), compact('dateIn'));
    }

    public function showDailyCollection($dateIn)
    {

        $checkInStartTime = Carbon::parse($dateIn)->addHours(5);
        $checkInEndTime = Carbon::parse($dateIn)->addDays()->addHours(4)->addMinute(59)->addSecond(59);
        $checkOutTime = Carbon::parse($dateIn)->addDay(1)->addHours(11);
        // dd($checkInStartTime, $checkInEndTime, $checkOutTime);
        $date = Carbon::parse($dateIn);


        $cards = Card::with('cardRooms', 'payments')
            ->where(function ($query) use ($checkInStartTime, $checkInEndTime, $checkOutTime, $date) {
                // 1. Cards arriving between 14 October 5 PM and 15 October 4:59 AM (Check-in time window)
                $query->whereBetween('arrival_date', [$checkInStartTime, $checkInEndTime])

                    // 2. Cards arriving before 14 October but staying past 14 October (departure is after 14 October)
                    ->orWhere(function ($subQuery) use ($checkInStartTime, $checkOutTime) {
                        $subQuery->where('arrival_date', '<', $checkInStartTime)  // Arriving before 14 October
                            ->where('departure_date', '>', $checkOutTime);  // Checkout by 15 October 11:00 AM
                    });
            })
            ->get();


        // dd($cards);

        // $filteredItems = []; // Initialize an empty array

        // foreach ($cards as $card) {
        //     $calculator = new DailyCollectionCalculator($card);
        //     $dailyCollection = $calculator->calculate();

        //     $filteredItem = $dailyCollection->first(function ($item) use ($date) {
        //         return Carbon::parse($item['specific_date'])->isSameDay($date);
        //     });

        //     if ($filteredItem) {
        //         $filteredItems[] = $filteredItem; // Add filtered item to the array
        //     }
        // }


        $filteredItems = collect(); // Initialize an empty collection

        foreach ($cards as $card) {
            $calculator = new DailyCollectionCalculator($card);
            $dailyCollection = $calculator->calculate();

            $filteredItem = $dailyCollection->first(function ($item) use ($date) {
                return Carbon::parse($item['specific_date'])->isSameDay($date);
            });

            if ($filteredItem) {
                $filteredItems->push($filteredItem); // Add filtered item to the collection
            }
        }

return $filteredItems;
        // dd($filteredItems);
        foreach ($filteredItems as $item) {
            echo $item['adv_adjust'];
        }


        $card = Card::with(['cardRooms', 'payments'])->findOrFail(6);
        // dd($card);

        // dd($dateIn);
        $calculator = new DailyCollectionCalculator($card);
        // $cal = new DailyCollectionService($card);
        $dailyCollection = $calculator->calculate();

        $dateIn = Carbon::parse("2025-02-16");
        $filteredItem = $dailyCollection->first(function ($item) use ($dateIn) {
            return Carbon::parse($item['specific_date'])->isSameDay($dateIn);
        });

        // dd($filteredItem);

        // dd($filteredArray);
        // return $dailyCollection;
        // return response()->json($dailyCollection);
    }
}
