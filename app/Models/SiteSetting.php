<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Field contract (site_settings table + fixup migration):
 * - key: string, unique, not null (e.g. 'system_name', 'logo', 'favicon')
 * - value: text, nullable (plain string or relative public path for logo/favicon)
 * - created_at / updated_at: timestamps
 *
 * Branding keys consumed with file-existence check by the view-sharing layer:
 * logo, favicon, system_name.
 */
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
