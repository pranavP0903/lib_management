<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // REMOVED incorrect primary key definition

    protected $fillable = [
        'action_type',
        'description',
        'performed_by'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    // Static helper method
    public static function log($action, $description, $performedBy = null)
    {
        return self::create([
            'action_type' => $action,
            'description' => $description,
            'performed_by' => $performedBy,
        ]);
    }
}