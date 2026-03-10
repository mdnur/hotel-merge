<?php

namespace App\Filament\Resources\Reservations\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions;
use App\Models\Reservation;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Reservations\ReservationResource;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('viewInfo')
                ->label('View Info')
                ->icon('heroicon-o-eye')
                ->action(function (Reservation $record) {
                    return redirect()->to(ReservationResource::getUrl('viewInfo', [$record]));
                }),

        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Reservation updated';
    }
}
