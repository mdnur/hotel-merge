<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reservation extends Model
{
    // protected $fillable = [
    //     'user_id',
    //     'customer_id',
    //     'payment_id',
    //     'reservation_no',
    //     'booking_date',
    //     'check_in',
    //     'check_out',
    //     'total',
    //     'status',
    // ];
    protected $guarded = [];

    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function payment()
    // {
    //     return $this->belongsTo(Payment::class);
    // }
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reservationRooms()
    {
        return $this->hasMany(ReservationRoom::class);
    }
}
