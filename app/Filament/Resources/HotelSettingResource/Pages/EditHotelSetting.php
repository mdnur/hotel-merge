<?php

namespace App\Filament\Resources\HotelSettingResource\Pages;

use App\Filament\Resources\HotelSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHotelSetting extends EditRecord
{
    protected static string $resource = HotelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
