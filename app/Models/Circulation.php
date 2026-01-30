<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Circulation extends Model
{
    protected $primaryKey = 'transaction_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'member_id',
        'copy_id',
        'issue_date',
        'due_date',
        'return_date',
        'status'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'status' => 'string'
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'copy_id', 'copy_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class, 'transaction_id', 'transaction_id');
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->status === 'ISSUED' && $this->due_date < now();
    }

    public function getOverdueDaysAttribute()
    {
        if (!$this->is_overdue) return 0;
        return now()->diffInDays($this->due_date);
    }

    public function getCalculatedFineAttribute()
    {
        if (!$this->is_overdue) return 0;
        
        $finePerDay = LibrarySetting::where('setting_key', 'FINE_PER_DAY')
            ->value('setting_value') ?? 5;
            
        return $this->overdue_days * $finePerDay;
    }
}