<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'room_id',
        'bed_id',
        'app_reference',
        'full_name',
        'phone',
        'email',
        'aadhaar_number',
        'pan_number',
        'parent_name',
        'parent_phone',
        'emergency_contact',
        'current_address',
        'joining_date',
        'kyc_status',
        'rent_status',
        'deposit_status',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function electricityReadings()
    {
        return $this->hasMany(ElectricityReading::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function registrationRequests()
    {
        return $this->hasMany(RegistrationRequest::class);
    }
}
