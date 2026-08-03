<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'branch_id',
        'tx_reference',
        'payment_type',
        'amount',
        'payment_mode',
        'payment_date',
        'due_date',
        'status',
        'verification_remarks',
        'rejection_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function proof()
    {
        return $this->hasOne(PaymentProof::class);
    }
}
