<?php

namespace App\Filament\Resources\HotelSettings\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\HotelSettings\HotelSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHotelSettings extends ListRecords
{
    protected static string $resource = HotelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
