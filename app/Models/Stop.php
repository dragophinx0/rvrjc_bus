<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    protected $fillable = [
        'route_id',
        'stop_name',
        'sequence',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // Relationships
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}
