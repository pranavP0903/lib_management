<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $primaryKey = 'book_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category',
        'publisher',
        'edition',
        'digital_resource_url'
    ];

    // Relationships
    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id', 'book_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'book_id', 'book_id');
    }

    public function circulations()
    {
        return $this->hasManyThrough(Circulation::class, BookCopy::class, 'book_id', 'copy_id');
    }

    // Helper Methods
    public function availableCopies()
    {
        return $this->copies()->where('status', 'AVAILABLE')->count();
    }

    public function totalCopies()
    {
        return $this->copies()->count();
    }
}