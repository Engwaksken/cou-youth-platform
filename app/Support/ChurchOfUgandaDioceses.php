<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\OrganisationUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * The dioceses of the Province of the Church of Uganda.
 *
 * Dioceses are stored as organisation units of type "diocese" so admins can
 * rename, add or deactivate them from the Organisation page. This list only
 * seeds the initial set.
 */
final class ChurchOfUgandaDioceses
{
    public const PROVINCE_CODE = 'COU';

    /** @var list<string> */
    public const NAMES = [
        'Ankole',
        'Bukedi',
        'Bunyoro-Kitara',
        'Busoga',
        'Central Buganda',
        'Central Busoga',
        'East Ruwenzori',
        'Kampala',
        'Karamoja',
        'Kigezi',
        'Kinkiizi',
        'Kitgum',
        'Kumi',
        'Lango',
        'Luweero',
        'Madi-West Nile',
        'Masindi-Kitara',
        'Mbale',
        'Mityana',
        'Muhabura',
        'Mukono',
        'Namirembe',
        'Nebbi',
        'North Ankole',
        'North Karamoja',
        'North Kigezi',
        'North Mbale',
        'North West Ankole',
        'Northern Uganda',
        'Ruwenzori',
        'Sebei',
        'Soroti',
        'South Ankole',
        'South Rwenzori',
        'West Ankole',
        'West Buganda',
        'West Lango',
    ];

    public static function code(string $name): string
    {
        return 'COU-DIO-'.Str::upper(Str::slug($name));
    }

    public static function label(string $name): string
    {
        return $name.' Diocese';
    }

    /**
     * Create any missing dioceses under the province. Safe to run repeatedly:
     * a diocese is skipped if one with the same name or code already exists.
     */
    public static function seed(): void
    {
        $now = now();

        $provinceId = DB::table('organisation_units')->where('type', 'province')->orderBy('id')->value('id')
            ?? DB::table('organisation_units')->insertGetId([
                'type' => 'province',
                'name' => 'Province of the Church of Uganda',
                'code' => DB::table('organisation_units')->where('code', self::PROVINCE_CODE)->exists() ? null : self::PROVINCE_CODE,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        $existingNames = DB::table('organisation_units')
            ->where('type', 'diocese')
            ->pluck('name')
            ->map(fn ($name) => mb_strtolower(trim((string) $name)))
            ->all();
        $existingCodes = DB::table('organisation_units')->whereNotNull('code')->pluck('code')->all();

        foreach (self::NAMES as $name) {
            $label = self::label($name);
            $code = self::code($name);

            if (in_array(mb_strtolower($label), $existingNames, true)
                || in_array(mb_strtolower($name), $existingNames, true)
                || in_array($code, $existingCodes, true)) {
                continue;
            }

            DB::table('organisation_units')->insert([
                'parent_id' => $provinceId,
                'type' => 'diocese',
                'name' => $label,
                'code' => $code,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /** Validation rule: the value must be an active diocese. */
    public static function existsRule(): Exists
    {
        return Rule::exists('organisation_units', 'id')
            ->where('type', 'diocese')
            ->where('is_active', true);
    }

    /** Active dioceses, alphabetically, for select inputs. */
    public static function options(): Collection
    {
        return OrganisationUnit::query()
            ->where('type', 'diocese')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
