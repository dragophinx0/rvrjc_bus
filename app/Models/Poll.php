<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'date', 'is_active'];

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
