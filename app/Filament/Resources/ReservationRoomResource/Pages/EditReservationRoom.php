<?php

namespace App\Filament\Resources\ReservationRoomResource\Pages;

use App\Filament\Resources\ReservationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservationRoom extends EditRecord
{
    protected static string $resource = ReservationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
