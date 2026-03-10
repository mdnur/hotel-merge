<?php

namespace App\Filament\Resources\ExpenseTypes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ExpenseTypes\ExpenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenseTypes extends ListRecords
{
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
