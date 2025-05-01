<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    // public function reservation()
    // {
    //     return $this->belongsTo(Reservation::class, 'payment_id');
    // }

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }
}
