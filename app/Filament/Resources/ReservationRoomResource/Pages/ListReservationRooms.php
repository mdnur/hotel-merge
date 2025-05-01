<?php

namespace App\Filament\Resources\ReservationRoomResource\Pages;

use App\Filament\Resources\ReservationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservationRooms extends ListRecords
{
    protected static string $resource = ReservationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
