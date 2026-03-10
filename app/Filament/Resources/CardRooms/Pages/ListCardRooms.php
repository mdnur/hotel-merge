<?php

namespace App\Filament\Resources\CardRooms\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\CardRooms\CardRoomResource;
use App\Filament\Resources\CardRooms\Widgets\CardsRoomStatsOverview;
use App\Models\CardRoom;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCardRooms extends ListRecords
{
    protected static string $resource = CardRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'Current Occupied' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->currentlyOccupied())->badge(CardRoom::query()->currentlyOccupied()->count()),
            'CheckOut Today' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->CheckoutToday())->badge(CardRoom::query()->checkoutToday()->count()),
            'Today Check in' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('check_in', [
                    Carbon::today()->addHours(5),                     // Today 5:00 AM
                    Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
                ]))->badge(CardRoom::query()->whereBetween('check_in', [
                    Carbon::today()->addHours(5),                     // Today 5:00 AM
                    Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
                ])->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CardsRoomStatsOverview::class,
        ];
    }
}
