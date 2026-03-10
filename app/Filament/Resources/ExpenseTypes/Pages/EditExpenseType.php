<?php

namespace App\Filament\Resources\ExpenseTypes\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ExpenseTypes\ExpenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseType extends EditRecord
{
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
