<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPER_ADMIN';
    }

    public function isSubAdmin(): bool
    {
        return $this->role === 'SUB_ADMIN';
    }

    public function isStudent(): bool
    {
        return $this->role === 'STUDENT';
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'sub_admin_branch', 'user_id', 'branch_id');
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }
}
