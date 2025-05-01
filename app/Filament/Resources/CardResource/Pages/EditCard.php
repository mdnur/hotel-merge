<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Models\Card;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCard extends EditRecord
{
    protected static string $resource = CardResource::class;

    protected $listeners = ['refreshParent' => 'refreshFormData'];

    public function refreshFormData(array $attributes = []): void
    {
        $this->record->refresh(); // Reload data from DB
        $this->fillForm();        // Refill form to trigger UI reactivity
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->action(function () {
                    // First delete related models
                    $this->record->payments()->delete(); // example
                    $this->record->cardRooms()->delete(); // example

                    // Then delete the main record

                    $this->record->delete();
                    Notification::make()
                        ->title('Card deleted SuccessFully.')
                        ->success()
                        ->send();

                    return redirect(\App\Filament\Resources\CardResource::getUrl());

                    // $this->notify('success', 'Card deleted successfully.');
                })
                ->color('danger')
                ->visible(fn () => auth()->user()->isSuperAdmin()) // 👈 Only visible if Superadmin
                ->icon('heroicon-o-trash')
                ->label(__('Delete Card')),

            Action::make('ViewCardAccounting')
                ->color('info')
                ->icon('heroicon-o-eye')
                ->label('View Card Accounting')
                ->modalHeading('Card Accounting Details')
                ->modalContent(function (Card $record) {
                    return view('accounting', ['record' => $record]);
                }) // 👈 load custom Blade view inside modal
                ->modalWidth('5xl') // optional: make modal bigger
                ->disabledForm()->modalCloseButton(true)->modalSubmitAction(false)->modalCancelActionLabel('Close'), // optional: add close button,

            Action::make('Checkout')
                ->visible(fn (Card $record) => is_null($record->departure_date)) // <--- only show if departure_date is null
                ->requiresConfirmation()
                ->action(fn (Card $record) => $record->update([
                    'departure_date' => now(),
                    'check_out_made_by' => auth()->id(),
                ]))
                ->action(function (Card $record) {
                    $now = now();
                    // dd($this->record->cardRooms);
                    $this->record->cardRooms->each(function ($room) use ($now) {
                        // dd($room->check_out, $record->departure_date);
                        if ($room->check_out > $now) {
                            $room->update([
                                'check_out' => $now,
                            ]);
                        }
                    });
                    $record->update([
                        'departure_date' => $now,
                        'check_out_made_by' => auth()->id(),
                    ]);
                    $this->record->refresh();
                    $this->fillForm();
                    $this->dispatch('refreshParent', $record);
                    $this->dispatch('refreshCardRoomsTable');         // 🔥 dispatch refresh for child relation manager

                    Notification::make()
                        ->title('Check Out made successfully.')
                        ->success()
                        ->send();
                })
                ->color('warning'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->refresh(); // Force fetch latest data from DB

        return parent::mutateFormDataBeforeFill($data);
    }
}
