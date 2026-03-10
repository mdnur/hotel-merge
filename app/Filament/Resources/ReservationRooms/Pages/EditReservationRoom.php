<?php

namespace App\Filament\Resources\ReservationRooms\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ReservationRooms\ReservationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservationRoom extends EditRecord
{
    protected static string $resource = ReservationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
