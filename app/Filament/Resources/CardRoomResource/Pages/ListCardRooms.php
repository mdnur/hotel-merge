<?php

namespace App\Filament\Resources\CardRoomResource\Pages;

use App\Filament\Resources\CardRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCardRooms extends ListRecords
{
    protected static string $resource = CardRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
