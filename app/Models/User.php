<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_COORDINATOR = 'bus_coordinator';
    const ROLE_DRIVER = 'driver';
    const ROLE_FACULTY = 'faculty';
    const ROLE_STUDENT = 'student';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'gender',
        'email',
        'mobile_number',
        'password',
        'role',
        'roll_number',
        'course',
        'branch_id',
        'year',
        'date_of_birth',
        'employee_id',
        'designation_id',
        'department_id',
        'is_verified',
        'otp_code',
        'otp_expires_at',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'date_of_birth' => 'date',
    ];

    // Helper methods for roles
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }
    public function isCoordinator()
    {
        return $this->role === self::ROLE_COORDINATOR;
    }
    public function isDriver()
    {
        return $this->role === self::ROLE_DRIVER;
    }
    public function isFaculty()
    {
        return $this->role === self::ROLE_FACULTY;
    }
    public function isStudent()
    {
        return $this->role === self::ROLE_STUDENT;
    }

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
    public function department()
    {
        return $this->belongsTo(Branch::class, 'department_id');
    }
    public function verificationRequests()
    {
        return $this->hasMany(VerificationRequest::class, 'user_id');
    }
    public function processedVerifications()
    {
        return $this->hasMany(VerificationRequest::class, 'processed_by');
    }
    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }
    public function seatReservations()
    {
        return $this->hasMany(SeatReservation::class);
    }

    public function generateOtp()
    {
        $this->otp_code = rand(100000, 999999);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();
        return $this->otp_code;
    }

    public function verifyOtp($code)
    {
        if ($this->otp_code === $code && $this->otp_expires_at->isFuture()) {
            $this->email_verified_at = now();
            $this->otp_code = null;
            $this->otp_expires_at = null;
            $this->save();
            return true;
        }
        return false;
    }

    // Existing verification logic
    public function canVerify(User $user)
    {
        if ($this->isAdmin() && $user->isCoordinator())
            return true;
        if ($this->isCoordinator() && ($user->isDriver() || $user->isFaculty()))
            return true;
        if ($this->isFaculty() && $user->isStudent())
            return $this->department_id === $user->branch_id;
        return false;
    }
}
