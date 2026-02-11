<?php

namespace App\Models;

use App\Events\BusLocationUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_number',
        'capacity',
        'layout_type',
        'rows',
        'is_active',
        'current_location_lat',
        'current_location_lng',
        'current_trip_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_location_lat' => 'double',
        'current_location_lng' => 'double',
    ];

    public function currentTrip()
    {
        return $this->belongsTo(BusTrip::class, 'current_trip_id');
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function trips()
    {
        return $this->hasMany(BusTrip::class);
    }

    public function locationHistory()
    {
        return $this->hasMany(BusLocationHistory::class);
    }

    public function getCachedLocation()
    {
        return Cache::get("bus_{$this->id}_location");
    }
}
