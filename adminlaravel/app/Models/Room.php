<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'floor_number',
        'room_number',
        'sharing_type',
        'max_beds',
        'is_ac',
        'description',
        'facilities',
        'images',
        'status',
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'max_beds' => 'integer',
        'is_ac' => 'boolean',
        'facilities' => 'array',
        'images' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function allocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }
}
