<?php

namespace App\Filament\Resources\ExpenseTypeResource\Pages;

use App\Filament\Resources\ExpenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenseTypes extends ListRecords
{
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
