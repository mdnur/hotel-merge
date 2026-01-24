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
        // return $query->whereBetween('check_out', [
        //     Carbon::today()->addHours(5),                     // Today 5:00 AM
        //     Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
        // ]);

        return $query
            ->whereBetween('check_out', [
                Carbon::today()->addHours(11)->minute(30),                     // Today 5:00 AM
                Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
            ])
            ->whereHas('card', function (Builder $q) {
                $q->whereBetween('departure_date', [
                    Carbon::today()->addHours(11)->minute(30),                     // Today 5:00 AM
                    Carbon::tomorrow()->addHours(4)->addMinutes(49), // Tomorrow 4:49 AM
                ]);
            });
    }
}
