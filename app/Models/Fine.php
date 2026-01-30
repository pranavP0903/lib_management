<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $primaryKey = 'fine_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'transaction_id',
        'fine_amount',
        'fine_status'
    ];

    protected $casts = [
        'fine_amount' => 'decimal:2',
        'fine_status' => 'string',
        'calculated_on' => 'datetime'
    ];

    // Relationships
    public function circulation(): BelongsTo
    {
        return $this->belongsTo(Circulation::class, 'transaction_id', 'transaction_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('fine_status', 'PENDING');
    }

    public function scopePaid($query)
    {
        return $query->where('fine_status', 'PAID');
    }
}