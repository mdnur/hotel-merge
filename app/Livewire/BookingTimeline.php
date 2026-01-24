<?php

namespace App\Livewire;

use Illuminate\Support\Carbon;
use Livewire\Component;

class BookingTimeline extends Component
{
    public $rooms;

    public $bookings;

    public $x = 'hello';

    public $days = [];

    public function mount()
    {
        // Example static data - replace with DB queries
        $this->rooms = [
            ['number' => '101', 'type' => '1 bed', 'status' => 'Ready'],
            ['number' => '102', 'type' => '1 bed', 'status' => 'Clean up'],
            ['number' => '103', 'type' => '1 bed', 'status' => 'Dirty'],
            ['number' => '104', 'type' => '1 bed', 'status' => 'Ready'],
            ['number' => '105', 'type' => '2 beds', 'status' => 'Ready'],
            // ... Add more rooms
        ];

        $this->bookings = [
            ['room' => '101', 'code' => 'A-12', 'start' => '2017-03-02', 'end' => '2017-03-23', 'status' => 'New', 'paid' => true],
            ['room' => '103', 'code' => 'A-45', 'start' => '2017-03-07', 'end' => '2017-03-21', 'status' => 'Confirmed', 'paid' => true],
            ['room' => '105', 'code' => 'A-58', 'start' => '2017-03-06', 'end' => '2017-03-14', 'status' => 'Arrived', 'paid' => true],
            // ... Add more bookings
        ];

        $this->days = collect(range(1, 31))->map(function ($day) {
            return Carbon::create(2017, 3, $day)->format('Y-m-d');
        });
    }

    public function render()
    {
        $this->mount();

        return view('livewire.booking-timeline');
    }
}
