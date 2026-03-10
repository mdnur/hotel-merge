<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CardRoomCalender extends Page
{
    // protected static string|Heroicon|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Room Calendar';

    protected static ?string $title = 'Room Availability';

    protected string $view = 'filament.pages.card-room-calender';

    public function rooms(): array
    {
        return [
            [
                'id' => 1,
                'title' => '101 | Eco Deluxe',
            ],
            [
                'id' => 2,
                'title' => '102 | Eco Deluxe',
            ],
            [
                'id' => 3,
                'title' => '201 | Deluxe Couple',
            ],
            [
                'id' => 4,
                'title' => '202 | Deluxe Couple',
            ],
            [
                'id' => 5,
                'title' => '301 | Super Deluxe Couple',
            ],
            [
                'id' => 6,
                'title' => '401 | Premium Couple',
            ],
        ];
    }

    public function bookings(): array
    {
        return [
            [
                'id' => 1,
                'resourceId' => 1,
                'title' => 'A-101',
                'start' => now()->toDateString(),
                'end' => now()->addDays(2)->toDateString(),
                'backgroundColor' => '#22c55e',
                'status' => 'CONFIRMED',
            ],
            [
                'id' => 2,
                'resourceId' => 3,
                'title' => 'A-201',
                'start' => now()->addDay()->toDateString(),
                'end' => now()->addDays(4)->toDateString(),
                'backgroundColor' => '#f59e0b',
                'status' => 'NEW',
            ],
            [
                'id' => 3,
                'resourceId' => 6,
                'title' => 'A-401',
                'start' => now()->addDays(3)->toDateString(),
                'end' => now()->addDays(6)->toDateString(),
                'backgroundColor' => '#3b82f6',
                'status' => 'ARRIVED',
            ],
        ];
    }
}
