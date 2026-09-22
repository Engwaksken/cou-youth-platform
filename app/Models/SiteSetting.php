<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    public static function get($key, $default = null)
    {
        try {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set($key, $value): void
    {
        try {
            static::updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        } catch (\Throwable $e) {
        }
    }
}
