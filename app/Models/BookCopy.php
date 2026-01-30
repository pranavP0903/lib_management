<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    protected $primaryKey = 'copy_id';
    public $incrementing = true;
    protected $keyType = 'int';

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
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }

    public function circulations(): HasMany
    {
        return $this->hasMany(Circulation::class, 'copy_id', 'copy_id');
    }

    public function currentCirculation()
    {
        return $this->circulations()->where('status', 'ISSUED')->first();
    }

    // Scope
    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }
}