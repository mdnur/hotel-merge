<?php

namespace App\Filament\Resources\CardRoomResource\Pages;

use App\Filament\Resources\CardRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardRoom extends EditRecord
{
    protected static string $resource = CardRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(auth()->user()->isSuperAdmin()),
        ];
    }
}
