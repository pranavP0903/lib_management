<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    protected $primaryKey = 'report_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'report_type',
        'generated_by'
    ];

    protected $casts = [
        'generated_on' => 'datetime',
        'report_type' => 'string'
    ];
}