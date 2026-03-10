<?php

namespace App\Filament\Resources\ReservationRooms\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ReservationRooms\ReservationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservationRooms extends ListRecords
{
    protected static string $resource = ReservationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
