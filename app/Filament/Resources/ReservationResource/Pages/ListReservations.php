<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Enums\Status;
use App\Filament\Resources\ReservationResource;
use App\Filament\Widgets\StatsOverview;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

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
            "Today Check in List" => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    // You can add conditions to modify the query here
                    // dd($query);
                    $query->where('check_in_date', Carbon::now()->format("Y-m-d"));
                })->badge(Reservation::query()->where('check_in_date',  Carbon::now()->format("Y-m-d"))->count()),
            "Tommorrow Check in List" => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    // You can add conditions to modify the query here
                    // dd($query);
                    $query->where('check_in_date', Carbon::tomorrow()->format("Y-m-d"));
                })->badge(Reservation::query()->where('check_in_date',  Carbon::tomorrow()->format("Y-m-d"))->count()),


            "Today Booking" => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    // You can add conditions to modify the query here
                    // dd($query);
                    $query->where('booking_date', Carbon::now()->format("Y-m-d"));
                })->badge(Reservation::query()->where('booking_date', Carbon::now()->format("Y-m-d"))->count()),
            "Confirm" => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {

                    $query->where('status', Status::Confirm);
                })->badge(Reservation::query()->where('status', Status::Confirm)->count()),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}
