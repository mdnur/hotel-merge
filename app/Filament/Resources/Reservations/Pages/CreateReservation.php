<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Customer;
use App\Models\Payment;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;

use function Filament\Support\is_app_url;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected static bool $canCreateAnother = false;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');
        } catch (Halt $exception) {
            return;
        }

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // return (($this->form->getRawState())['Rooms']);
        $data['user_id'] = auth()->user()->id;

        $s = ($this->form->getRawState())['customer'];
        $ss = Customer::where('phone', $s['phone'])->first();

        if ($ss) {
            $data['customer_id'] = $ss->id;
        } else {
            $customer = new Customer(($this->form->getRawState())['customer']);
            $customer->save();
            $data['customer_id'] = $customer->id;
        }

        // For customer

        // // For payment type
        // $payment = new Payment(($this->form->getRawState())['payment']);
        // $payment->save();
        // // $data['payment_id'] = $payment->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Reservation created';
    }
}
