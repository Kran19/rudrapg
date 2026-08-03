<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'room_id',
        'student_id',
        'reading_month',
        'current_reading',
        'previous_reading',
        'units_consumed',
        'unit_rate',
        'total_amount',
        'meter_photo_path',
        'status',
        'audited_by',
    ];

    protected $casts = [
        'current_reading' => 'integer',
        'previous_reading' => 'integer',
        'units_consumed' => 'integer',
        'unit_rate' => 'float',
        'total_amount' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
