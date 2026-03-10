<?php

namespace App\Filament\Resources\ExpenseTypes\Pages;

use App\Filament\Resources\ExpenseTypes\ExpenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseType extends CreateRecord
{
    protected static string $resource = ExpenseTypeResource::class;
    protected static bool $canCreateAnother = false;
}
