<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRoom extends Model
{
    use HasFactory;

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    protected $guarded = [];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* 🔥 CURRENTLY OCCUPIED ROOMS */
    public function scopeCurrentlyOccupied(Builder $query)
    {
        return $query
            ->where('check_out', '>', now())
            ->where('check_in', '<', now())
            ->whereHas('card', function (Builder $q) {
                $q->whereNull('departure_date');
            });
    }

    public function scopeCheckoutToday(Builder $query)
    {
        $now = Carbon::now();
        Carbon::setTestNowAndTimezone($now->copy());
        // Determine hotel business date
        $businessDate = $now->between(
            Carbon::today()->startOfDay(),
            Carbon::today()->setHour(11)->setMinute(15)
        )
            ? Carbon::yesterday()
            : Carbon::today();

        // Hotel checkout window (11:30 AM → next day 11:29 AM)
        $start = $businessDate->copy()->setHour(11)->setMinute(30);
        $end = $businessDate->copy()->addDay()->setHour(11)->setMinute(29);

        return $query
            ->whereBetween('check_out', [$start, $end])
            ->whereHas('card', function (Builder $q) use ($start, $end) {
                $q->whereBetween('departure_date', [$start, $end]);
            });
    }

    public function scopeTodaysTotalRoom(Builder $query)
    {
        $now = Carbon::now();

        $businessDate = $now->between(
            Carbon::today()->startOfDay(),
            Carbon::today()->setHour(11)->setMinute(15)
        )
            ? Carbon::yesterday()
            : Carbon::today();

        $start = $businessDate->copy()->setHour(11)->setMinute(30);
        $end = $businessDate->copy()->addDay()->setHour(11)->setMinute(29);

        return $query->where(function (Builder $q) use ($start, $end) {

            // 1️⃣ Currently Occupied
            $q->where(function (Builder $occupied) {
                $occupied
                    ->where('check_out', '>', now())
                    ->where('check_in', '<', now())
                    ->whereHas('card', fn ($c) => $c->whereNull('departure_date'));
            })

            // OR

            // 2️⃣ Checkout Today
                ->orWhere(function (Builder $checkout) use ($start, $end) {
                    $checkout
                        ->whereBetween('check_out', [$start, $end])
                        ->whereHas('card', fn ($c) => $c->whereBetween('departure_date', [$start, $end])
                        );
                });
        });
    }

    public function scopeCustomDateTotalRoom(Builder $query, Carbon|string $date)
    {
        $date = Carbon::parse($date);

        /*
         |---------------------------------------------------------
         | Business day logic
         | Business day = 11:30 AM to next day 11:29 AM
         |---------------------------------------------------------
         */

        $start = $date->copy()->setHour(11)->setMinute(30)->startOfMinute();
        $end = $date->copy()->addDay()->setHour(11)->setMinute(29)->endOfMinute();

        return $query->where(function (Builder $q) use ($start, $end) {

            // 1️⃣ Occupied during business time
            $q->where(function (Builder $occupied) use ($start, $end) {
                $occupied
                    ->where('check_in', '<', $end)
                    ->where('check_out', '>', $start)
                    ->whereHas('card', function ($c) {
                        $c->whereNull('departure_date');
                    });
            })

            // OR

            // 2️⃣ Checkout within business window
                ->orWhere(function (Builder $checkout) use ($start, $end) {
                    $checkout
                        ->whereBetween('check_out', [$start, $end])
                        ->whereHas('card', function ($c) use ($start, $end) {
                            $c->whereBetween('departure_date', [$start, $end]);
                        });
                });
        });
    }
}
