<?php

namespace App\Models;

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
}
