<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    // REMOVED incorrect primary key definition

    protected $fillable = [
        'circulation_id',
        'fine_amount',
        'status'
    ];

    protected $casts = [
        'fine_amount' => 'decimal:2',
        'status' => 'string',
        'calculated_on' => 'datetime'
    ];

    // Relationships
    public function circulation(): BelongsTo
    {
        return $this->belongsTo(Circulation::class, 'circulation_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }
}