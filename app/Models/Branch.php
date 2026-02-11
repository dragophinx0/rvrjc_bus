<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'course_type'];

    // Relationships
    public function students()
    {
        return $this->hasMany(User::class, 'branch_id')
            ->where('role', User::ROLE_STUDENT);
    }

    public function faculty()
    {
        return $this->hasMany(User::class, 'department_id')
            ->where('role', User::ROLE_FACULTY);
    }
}
