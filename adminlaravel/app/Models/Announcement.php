<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'title',
        'content',
        'category',
        'priority',
        'is_important',
        'expiry_date',
        'created_by',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
