<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'route_id',
        'stop_id',
        'shift',
        'arrival_time',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'arrival_time' => 'datetime:H:i',
        ];
    }

    // Relationships
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeFirstShift($query)
    {
        return $query->where('shift', 'first');
    }

    public function scopeSecondShift($query)
    {
        return $query->where('shift', 'second');
    }
}
