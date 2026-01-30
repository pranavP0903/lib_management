<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $primaryKey = 'log_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'action_type',
        'description',
        'performed_by'
    ];

    protected $casts = [
        'performed_on' => 'datetime'
    ];

    // Helper Methods
    public static function log($action, $description, $performedBy = null)
    {
        return self::create([
            'action_type' => $action,
            'description' => $description,
            'performed_by' => $performedBy,
            'performed_on' => now()
        ]);
    }
}