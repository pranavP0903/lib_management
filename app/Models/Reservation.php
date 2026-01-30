<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $primaryKey = 'reservation_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'member_id',
        'book_id',
        'status'
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'status' => 'string'
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }

    // Scopes
    public function scopeWaiting($query)
    {
        return $query->where('status', 'WAITING');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['WAITING', 'ALLOCATED']);
    }
}