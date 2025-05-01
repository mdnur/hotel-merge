<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Reservation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ReservationCountChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = null;
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        $startDate = !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            Carbon::now()->startOfMonth();

        $endDate = !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        if ($startDate->format('F Y') === $endDate->format('F Y')) {
            return $startDate->format('F Y');
        }

        return $startDate->format('F Y') . ' - ' . $endDate->format('F Y');
    }

    protected function getData(): array
    {
        $startDate = !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            Carbon::now()->startOfMonth();

        $endDate = !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        // Fetch reservations within the date range
        $reservations = Reservation::whereBetween('created_at', [$startDate, $endDate])->get();

        // Initialize data array with zeros for each day in the range
        $data = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $data->put($date->format('j'), 0);
        }

        // Count reservations and fill the data array
        $reservations->groupBy(function ($reservation) {
            return Carbon::parse($reservation->created_at)->format('j');
        })->each(function ($dayReservations, $day) use (&$data) {
            $data[$day] = $dayReservations->count();
        });

        // Ensure all values are non-negative (though they should be zero or positive by initialization)
        $data = $data->map(function ($count) {
            return max($count, 0);
        });

        return [
            'labels' => $data->keys()->toArray(),
            'datasets' => [
                [
                    'label' => 'Reservations Count by day',
                    'data' => $data->values()->toArray(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
