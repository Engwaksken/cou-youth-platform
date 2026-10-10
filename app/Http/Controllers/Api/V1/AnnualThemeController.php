<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\Branding\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class AnnualThemeController extends Controller
{
    public function __invoke(BrandingService $branding): JsonResponse
    {
        $theme = null;

        if (Schema::hasTable('annual_themes')) {
            $query = DB::table('annual_themes');

            if (Schema::hasColumn('annual_themes', 'is_published')) {
                $query->where('is_published', true);
            }

            $theme = $query
                ->orderByRaw('CASE WHEN year = ? THEN 0 ELSE 1 END', [(int) now()->year])
                ->orderByDesc('year')
                ->first();
        }

        $themeData = null;

        if ($theme) {
            $themeData = [
                'id' => (int) $theme->id,
                'year' => isset($theme->year) ? (int) $theme->year : null,
                'theme' => (string) ($theme->theme ?? ''),
                'scripture_reference' => $theme->scripture_reference ?? null,
                'description' => $theme->description ?? null,
                'image_url' => ! empty($theme->image_path)
                    ? $this->absoluteUrl(Storage::disk('public')->url((string) $theme->image_path))
                    : null,
            ];
        }

        $brandingData = $branding->data();
        $systemName = trim((string) SiteSetting::get('system_name', ''));
        if ($systemName !== '') {
            $brandingData['name'] = $systemName;
            $brandingData['short_name'] = $systemName;
        }
        if (! empty($brandingData['logo_url'])) {
            $brandingData['logo_url'] = $this->absoluteUrl((string) $brandingData['logo_url']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mission' => SiteSetting::get('mission'),
                'vision' => SiteSetting::get('vision'),
                'annual_theme' => $themeData,
                'branding' => $brandingData,
            ],
        ]);
    }

    private function absoluteUrl(string $value): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url('/'.ltrim($value, '/'));
    }
}
