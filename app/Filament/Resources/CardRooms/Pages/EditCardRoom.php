<?php

namespace App\Filament\Resources\CardRooms\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use App\Filament\Resources\Cards\Pages\EditCard;
use App\Filament\Resources\CardRooms\CardRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardRoom extends EditRecord
{
    protected static string $resource = CardRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(auth()->user()->isSuperAdmin()),
            ViewAction::make(),
            Action::make('ViewFullCard')->action(function () {
                return redirect(EditCard::getUrl(['record' => $this->record->card_id]));
            })->label('FullCard Details')->icon('heroicon-o-eye'),
        ];
    }
}
