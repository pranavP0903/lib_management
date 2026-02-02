<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\LibrarySetting;

class Circulation extends Model
{
    /**
     * Explicit table name (IMPORTANT)
     */
    protected $table = 'circulation';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'member_id',
        'copy_id',          // change to 'book_copy_id' ONLY if your DB column uses that
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'renewals',
        'notes'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'return_date' => 'date',
        'status'      => 'string'
    ];

    /**
     * Enable timestamps
     */
    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'copy_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class, 'circulation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'ISSUED'
            && $this->due_date !== null
            && $this->due_date->lt(now());
    }

    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function getLoanDurationAttribute()
{
    if (!$this->return_date) {
        return now()->diffInDays($this->issue_date);
    }

    return $this->return_date->diffInDays($this->issue_date);
}


    public function getCalculatedFineAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        $finePerDay = (int) LibrarySetting::getValue('FINE_PER_DAY', 5);

        return $this->overdue_days * $finePerDay;
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'ISSUED' || $this->due_date === null) {
            return 0;
        }

        $days = now()->diffInDays($this->due_date, false);
        return max(0, $days);
    }

    public function getIsDueSoonAttribute(): bool
    {
        if ($this->status !== 'ISSUED' || $this->due_date === null) {
            return false;
        }

        $daysUntilDue = now()->diffInDays($this->due_date, false);
        return $daysUntilDue <= 3 && $daysUntilDue > 0;
    }
}
