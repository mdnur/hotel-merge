<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRoom extends Model
{
    use HasFactory;

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

    public function checkOut($query) {}
}
