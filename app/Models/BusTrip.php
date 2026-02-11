<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'route_id',
        'driver_id',
        'shift',
        'trip_type',
        'boarding_started_at',
        'journey_started_at',
        'completed_at',
        'start_lat',
        'start_lng'
    ];

    protected $casts = [
        'boarding_started_at' => 'datetime',
        'journey_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function reservations()
    {
        return $this->hasMany(SeatReservation::class);
    }

    public function isBoarding()
    {
        return $this->boarding_started_at && !$this->journey_started_at;
    }

    public function isRunning()
    {
        return $this->journey_started_at && !$this->completed_at;
    }
}
