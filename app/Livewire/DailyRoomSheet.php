<?php

namespace App\Livewire;

use App\Http\Controllers\DailyCollectionCalculator;
use App\Models\Card;
use App\Models\Expense;
use App\Models\Setting;
use Carbon\Carbon;
use Livewire\Component;

class DailyRoomSheet extends Component
{
    public $date;

    public function save()
    {
        return $this->render();
    }

    public function render($dateIn = null)
    {
        $dateIn = $this->date ?? now()->format('Y-m-d');

        $data = $this->showDailyCollection($dateIn);
        dd($data);

        return view('livewire.daily-room-sheet', [
            'data' => $data,
            'dateIn' => $dateIn,
        ]);
    }

    public function showDailyCollection($dateIn)
    {
        $date = Carbon::parse($dateIn);

        $cards = $this->getCardsForDailySlices($dateIn);

        // dd($cards);
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
    }

    protected function getCardsForDailySlices($dateIn)
    {
        $check_in_start_time = Setting::where('key', '=', 'check_in_start_time')->get()->first()->value;
        $filterCheckInStartTime = explode(':', $check_in_start_time);

        $checkInStartTime = Carbon::parse($dateIn)->addHours((int) $filterCheckInStartTime[0])->addMinute((int) $filterCheckInStartTime[1]);

        $check_in_end_time = Setting::where('key', '=', 'check_in_end_time')->get()->first()->value;
        $filterCheckInEndTime = explode(':', $check_in_end_time);

        $checkInEndTime = Carbon::parse($dateIn)->addDays()->addHours((int) $filterCheckInEndTime[0])->addMinute((int) $filterCheckInEndTime[1])->addSecond(59);
        // $checkOutTime = Carbon::parse($dateIn)->addDay(1)->addHours(11);

        $check_out_time = Setting::where('key', '=', 'check_out_time')->get()->first()->value;
        $filterCheckOutTime = explode(':', $check_out_time);

        $checkOutCutoff = Carbon::parse($dateIn)->addHours((int) $filterCheckOutTime[0])->addMinute((int) $filterCheckOutTime[1]);

        // dd($checkInStartTime, $checkInEndTime, $checkOutTime);
        // dd($checkInStartTime, $checkInEndTime, $checkOutTime);
        return $cards = Card::where(function ($query) use ($checkInStartTime, $checkInEndTime, $checkOutCutoff) {
            $query->where('arrival_date', '>=', $checkInStartTime)
                ->where('arrival_date', '<', $checkInEndTime)
                ->orWhere(function ($subQuery) use ($checkInStartTime, $checkOutCutoff) {
                    $subQuery->where('arrival_date', '<', $checkInStartTime)
                        ->where(function ($q) use ($checkOutCutoff) {
                            $q->where('departure_date', '>=', $checkOutCutoff)
                                ->orWhereNull('departure_date'); // Include rows where departure_date is null
                        });
                });
        })->get();

    }

    public function expenditure($dateIn)
    {
        $date = Carbon::parse($dateIn);
        $expenses = Expense::where('expense_date', $date)->get();

        return $expenses;
    }
}
