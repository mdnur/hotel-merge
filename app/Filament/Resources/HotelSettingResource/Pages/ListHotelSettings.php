<?php

namespace App\Filament\Resources\HotelSettingResource\Pages;

use App\Filament\Resources\HotelSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHotelSettings extends ListRecords
{
    protected static string $resource = HotelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
