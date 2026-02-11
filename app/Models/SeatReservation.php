<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_trip_id',
        'seat_id',
        'user_id',
        'status',
        'expires_at',
        'confirmed_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(BusTrip::class, 'bus_trip_id');
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->status === 'reserved' && $this->expires_at && $this->expires_at->isPast();
    }
}
