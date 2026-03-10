<?php

namespace App\Filament\Resources\HotelSettings\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\HotelSettings\HotelSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHotelSetting extends EditRecord
{
    protected static string $resource = HotelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
