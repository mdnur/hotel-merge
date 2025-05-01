<?php

namespace App\Livewire;

use Livewire\Component;

class RoomBookingChart extends Component
{
    public $rooms = [];

    public $dates = [];

    public $bookings = [];

    public function mount()
    {
        $this->rooms = [
            ['number' => '101'], ['number' => '102'], ['number' => '103'], // Add all rooms
        ];

        $startDate = now();
        for ($i = 0; $i < 7; $i++) {
            $this->dates[] = $startDate->copy()->addDays($i)->format('d-M-Y');
        }

        $this->bookings = [
            // [room => room_number, date => 'dd-MMM-YYYY', status => 'occupied|reservation|checkout|out-of-order']
            ['room' => '101', 'date' => now()->format('d-M-Y'), 'status' => 'occupied'],
            ['room' => '102', 'date' => now()->addDays(1)->format('d-M-Y'), 'status' => 'reservation'],
        ];
    }

    public function getCellStatus($room, $date)
    {
        foreach ($this->bookings as $booking) {
            if ($booking['room'] === $room && $booking['date'] === $date) {
                return $booking['status'];
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.room-booking-chart');
    }
}
