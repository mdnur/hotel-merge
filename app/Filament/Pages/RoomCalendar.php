<?php

namespace App\Filament\Pages;

use App\Models\Room;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class RoomCalendar extends Page
{
    protected string $view = 'filament.pages.room-calendar';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::DocumentText;

    public array $resources = [];

    public array $events = [];

    public function mount(): void
    {
        $rooms = Room::with('cardRooms')->get();

        // Rooms → calendar rows
        $this->resources = $rooms->map(function ($room) {
            return [
                'id' => (string) $room->id,
                'title' => 'Room '.$room->room_no,
            ];
        })->values()->toArray();

        // Card rooms → calendar events
        $this->events = $rooms->flatMap(function ($room) {
            return $room->cardRooms->map(function ($cr) use ($room) {
                return [
                    'resourceId' => (string) $room->id,
                    'title' => 'C'.$cr->card->card_no,
                    'start' => $cr->check_in->toDateString(),
                    'end' => $cr->check_out->toDateString(),
                    'backgroundColor' => $cr->check_out > now() ? 'red' : 'yellow',
                    'textColor' => $cr->check_out > now() ? 'black' : 'black',
                ];
            });
        })->values()->toArray();

        // dd($this->resources, $this->events);
    }
}
