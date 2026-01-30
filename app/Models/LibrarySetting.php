<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $primaryKey = 'setting_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'setting_key',
        'setting_value'
    ];

    protected $casts = [
        'updated_at' => 'datetime'
    ];

    // Helper Methods
    public static function getValue($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}