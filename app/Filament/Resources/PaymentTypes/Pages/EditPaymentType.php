<?php

namespace App\Filament\Resources\PaymentTypes\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PaymentTypes\PaymentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentType extends EditRecord
{
    protected static string $resource = PaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
