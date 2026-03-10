<?php

namespace App\Filament\Resources\ReservationRooms\Pages;

use App\Filament\Resources\ReservationRooms\ReservationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReservationRoom extends CreateRecord
{
    protected static string $resource = ReservationRoomResource::class;
}
