<?php

namespace App\Filament\Resources\PaymentTypes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PaymentTypes\PaymentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTypes extends ListRecords
{
    protected static string $resource = PaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
