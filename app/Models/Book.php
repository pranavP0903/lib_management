<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Book extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category',
        'publisher',
        'edition',
        'digital_resource_url',
        'image_path',
        'release_mode',
        'release_at'
    ];

    protected $casts = [
        'release_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A book has many physical copies
     */
    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }

    /**
     * A book can have many reservations
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'book_id');
    }

    /**
     * A book can have many circulations through book copies
     */
    public function circulations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Circulation::class,
            BookCopy::class,
            'book_id',   // FK on book_copies table
            'copy_id',   // FK on circulation table
            'id',        // PK on books table
            'id'         // PK on book_copies table
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get available copies count
     */
    public function availableCopies(): int
    {
        return $this->copies()
            ->where('status', 'AVAILABLE')
            ->count();
    }

    /**
     * Get total copies count
     */
    public function totalCopies(): int
    {
        return $this->copies()->count();
    }

    /**
     * Whether the book is released and available to be issued/reserved
     */
    public function isReleased(): bool
    {
        // If release_mode is scheduled and release_at is in future, it's not released
        if ($this->release_mode === 'SCHEDULED' && $this->release_at) {
            return $this->release_at->lessThanOrEqualTo(now());
        }

        return true;
    }
}
