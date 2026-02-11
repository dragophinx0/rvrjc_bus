<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusLocationHistory extends Model
{
    protected $fillable = [
        'bus_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'speed' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    // Relationships
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Default ordering
    protected static function booted()
    {
        static::addGlobalScope('order', function ($query) {
            $query->orderBy('recorded_at', 'desc');
        });
    }
}
