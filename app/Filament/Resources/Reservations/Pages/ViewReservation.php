<?php

namespace App\Filament\Resources\Reservations\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
