<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'Current Occupied' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('departure_date', null)),
            'Today Check in' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('arrival_date', [
                    Carbon::today()->addHours(5),                     // Today 5:00 AM
                    Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
                ])
                ),
        ];
    }
}
