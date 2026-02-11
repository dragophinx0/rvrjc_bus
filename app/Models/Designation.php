<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function faculty()
    {
        return $this->hasMany(User::class, 'designation_id')
            ->where('role', User::ROLE_FACULTY);
    }
}
