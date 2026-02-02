<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    // REMOVED incorrect primary key definition

    protected $fillable = [
        'hrms_user_id',
        'full_name',
        'member_type',
        'email',
        'phone',
        'borrow_limit',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'borrow_limit' => 'integer'
    ];

    // Relationships
    public function circulations(): HasMany
    {
        return $this->hasMany(Circulation::class, 'member_id');
    }

    public function activeBorrowings()
    {
        return $this->circulations()->where('status', 'ISSUED');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'member_id');
    }

    public function fines()
    {
        return $this->hasManyThrough(Fine::class, Circulation::class, 'member_id', 'circulation_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    // Accessors
    public function getPendingFinesAttribute()
    {
        return $this->fines()  
            ->where('fines.status', 'PENDING')
            ->sum('fine_amount');
    }

    public function getOverdueBorrowingsAttribute()
    {
        return $this->circulations()
            ->where('status', 'ISSUED')
            ->where('due_date', '<', now())
            ->count();
    }
}