<?php

namespace App\Filament\Resources\CardRoomResource\Widgets;

use App\Models\CardRoom;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CardsRoomStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Rooms today', CardRoom::TodaysTotalRoom()->count()),
        ];
    }
}
