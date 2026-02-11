<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'name',
        'type',
        'bus_id',
        'created_by',
    ];

    // Relationships
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stops()
    {
        return $this->hasMany(Stop::class)->orderBy('sequence');
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    // Scopes
    public function scopePickup($query)
    {
        return $query->where('type', 'pickup');
    }

    public function scopeDrop($query)
    {
        return $query->where('type', 'drop');
    }
}
