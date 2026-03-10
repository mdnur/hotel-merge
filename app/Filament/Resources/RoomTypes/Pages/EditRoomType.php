<?php

namespace App\Filament\Resources\RoomTypes\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\RoomTypes\RoomTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomType extends EditRecord
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
