<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    // REMOVED incorrect primary key definition

    protected $fillable = [
        'setting_key',
        'setting_value'
    ];

    protected $casts = [
        'updated_at' => 'datetime'
    ];

    // Static helper methods
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