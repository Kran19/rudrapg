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
        'rent_balance_due',
        'deposit_status',
        'deposit_balance_due',
        'deposit_refund_status',
        'deposit_refund_amount',
        'deposit_refund_date',
        'deposit_refund_reference',
        'exit_date',
        'exit_reason',
        'exit_remarks',
        'last_room_number',
        'last_bed_code',
        'vacated_by',
        'initial_meter_reading',
        'initial_meter_photo',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'exit_date' => 'date',
        'deposit_refund_date' => 'date',
        'deposit_refund_amount' => 'float',
        'rent_balance_due' => 'float',
        'deposit_balance_due' => 'float',
        'initial_meter_reading' => 'float',
    ];

    public function getEffectiveRentDueAttribute(): float
    {
        if (strtoupper($this->rent_status ?? '') === 'PAID') {
            return 0.0;
        }
        if ((float)($this->rent_balance_due ?? 0) > 0) {
            return (float) $this->rent_balance_due;
        }
        if (strtoupper($this->rent_status ?? '') === 'DUE' && $this->bed) {
            return (float) ($this->bed->monthly_rent ?? 0);
        }
        return (float) ($this->rent_balance_due ?? 0);
    }

    public function getEffectiveDepositDueAttribute(): float
    {
        if (in_array(strtoupper($this->deposit_status ?? ''), ['PAID', 'VERIFIED'])) {
            return 0.0;
        }
        return (float) ($this->rent_balance_due ?? 0);
        if ((float)($this->deposit_balance_due ?? 0) > 0) {
            return (float) $this->deposit_balance_due;
        }
        if ($this->bed) {
            return (float) ($this->bed->security_deposit ?? 0);
        }
        return 0.0;
    }

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

    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class);
    }

    public function roomAllocations()
    {
        return $this->hasMany(RoomAllocation::class, 'student_id');
    }

    public function vacatedByUser()
    {
        return $this->belongsTo(User::class, 'vacated_by');
    }
}
