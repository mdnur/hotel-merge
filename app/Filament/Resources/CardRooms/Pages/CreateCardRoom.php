<?php

namespace App\Filament\Resources\CardRooms\Pages;

use App\Filament\Resources\CardRooms\CardRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCardRoom extends CreateRecord
{
    protected static string $resource = CardRoomResource::class;
    protected static bool $canCreateAnother = false;

}
