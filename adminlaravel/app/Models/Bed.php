<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'bed_code',
        'monthly_rent',
        'security_deposit',
        'status',
    ];

    protected $casts = [
        'monthly_rent' => 'float',
        'security_deposit' => 'float',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
