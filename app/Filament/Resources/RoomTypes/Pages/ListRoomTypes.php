<?php

namespace App\Filament\Resources\RoomTypes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\RoomTypes\RoomTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomTypes extends ListRecords
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
