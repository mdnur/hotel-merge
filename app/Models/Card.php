<?php

namespace App\Models;

use App\Http\Controllers\DailyCollectionCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Card extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function cardRooms()
    {
        return $this->hasMany(CardRoom::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }

    public function check_in_made_by_user()
    {
        return $this->belongsTo(User::class, 'check_in_made_by', 'id');
    }

    public function check_out_made_by_user()
    {
        return $this->belongsTo(User::class, 'check_out_made_by', 'id');
    }

    public function checkOutUser()
    {
        return $this->belongsTo(User::class, 'check_out_made_by');
    }

    public function getTotalRentAttribute()
    {
        $card = Card::with(['cardRooms'])->findOrFail($this->id);

        $calculator = new DailyCollectionCalculator($card);
        $dailyCollection = $calculator->getTotalRent();

        // return $this->cardRooms()->sum('rent');

        return $dailyCollection;
    }

    public function updateTotalRent()
    {
        $this->total_rent = $this->getTotalRentAttribute();
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
