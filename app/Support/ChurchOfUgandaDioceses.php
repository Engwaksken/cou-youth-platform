<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\OrganisationUnit;
use Illuminate\Support\Collection;
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
