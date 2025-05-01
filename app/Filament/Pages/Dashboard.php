<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')->native(false)->default(Carbon::now()->startOfMonth()->format('Y-m-d')),
                        DatePicker::make('endDate')->native(false)->maxDate(now()->format('Y-m-d'))->default(now()->format('Y-m-d')),
                        DatePicker::make('month')->native(false)->maxDate(now()->format('Y-m'))->default(now()->format('Y-m-d'))->label('Select Month'),                      // ...
                    ])
                    ->columns(3),
            ]);
    }
}
