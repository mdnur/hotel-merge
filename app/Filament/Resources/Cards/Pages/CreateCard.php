<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCard extends CreateRecord
{
    protected static string $resource = CardResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // return (($this->form->getRawState())['Rooms']);

        $data['check_in_made_by'] = auth()->user()->id;
        $data['check_out_made_by'] = null;

        return $data;
    }
}
