<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'google_map_link',
        'phone',
        'email',
        'manager_name',
        'manager_phone',
        'electricity_unit_rate',
        'qr_code_hash',
        'amenities',
        'branch_rules',
        'images',
        'status',
    ];

    protected $casts = [
        'electricity_unit_rate' => 'float',
        'amenities' => 'array',
        'images' => 'array',
    ];

    public function subAdmins()
    {
        return $this->belongsToMany(User::class, 'sub_admin_branch', 'branch_id', 'user_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function beds()
    {
        return $this->hasManyThrough(Bed::class, Room::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
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

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
