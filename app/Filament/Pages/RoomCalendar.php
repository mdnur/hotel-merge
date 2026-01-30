<?php

namespace App\Filament\Pages;

use App\Models\Room;
use Filament\Pages\Page;

class RoomCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Room Calendar';

    protected static ?string $title = 'Room Availability';

    protected static string $view = 'filament.pages.room-calendar';

    public function getRooms()
    {
        return Room::with('cardRooms')->orderBy('room_no', 'desc')->get();
    }
}
