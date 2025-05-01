<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        // $month = !is_null($this->filters['month'] ?? null) ?
        //     Carbon::parse($this->filters['month'])->format('m') :
        //     Carbon::now()->format('m');


        $month = !is_null($this->filters['month'] ?? null) ?
            Carbon::parse($this->filters['month'])->format('m') :
            Carbon::now()->format('m');

        $year = !is_null($this->filters['month'] ?? null) ?
            Carbon::parse($this->filters['month'])->format('Y') :
            Carbon::now()->format('Y');
        $monthCreate = Carbon::createFromDate(null, $month, 1);
        $formattedMonth = $monthCreate->format('F Y');

        // $totalBooking = Reservation::whereMonth('created_at', Carbon::now()->format('m'))->get()->count();
        $totalBooking = Reservation::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->get()->count();
        $myBooking = Reservation::whereMonth('created_at', $month)->whereYear('created_at',$year)->where('user_id', auth()->user()->id)->get()->count();
        $percentage = ($totalBooking > 0) ? number_format($myBooking / $totalBooking * 100, 2) : 0;
        $totalBookingToday = Reservation::whereDate('created_at', Carbon::today())->where('user_id', auth()->user()->id)->get()->count();
        return [
            Stat::make("Total Reservation {$formattedMonth}", $totalBooking),
            Stat::make("Total My Booking {$formattedMonth}",  $percentage . "%"),
            Stat::make("Total My Booking Today",  $totalBookingToday),
        ];
    }
}
