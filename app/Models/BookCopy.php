<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id',
        'copy_number',
        'status',
        'location'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Relationships
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }

    public function circulations(): HasMany
    {
        return $this->hasMany(Circulation::class, 'copy_id', 'id');
    }

    public function currentCirculation(): HasOne
    {
        // Return a relationship for the currently issued circulation (if any)
        return $this->hasOne(Circulation::class, 'copy_id', 'id')
                    ->where('status', 'ISSUED');
    }

    // Scope
    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }
}