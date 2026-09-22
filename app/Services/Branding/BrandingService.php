<?php

declare(strict_types=1);

namespace App\Services\Branding;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class BrandingService
{
    public function data(): array
    {
        return [
            'name' => (string) config('branding.name'),
            'short_name' => (string) config('branding.short_name'),
            'tagline' => (string) config('branding.tagline'),
            'primary_color' => (string) config('branding.primary_color'),
            'secondary_color' => (string) config('branding.secondary_color'),
            'support_email' => config('branding.support_email'),
            'logo_url' => $this->logoUrl(),
        ];
    }

    public function logoUrl(): ?string
    {
        return Cache::remember('platform_branding_logo_url', now()->addMinutes(10), function (): ?string {
            $disk = Storage::disk('public');
            $configured = trim((string) config('branding.logo_path', ''));

            if ($configured !== '' && $disk->exists($configured)) {
                return $disk->url($configured);
            }

            $preferred = [
                'branding/logo.png',
                'branding/logo.jpg',
                'branding/logo.jpeg',
                'branding/logo.webp',
                'branding/logo.svg',
                'system/logo.png',
                'system/logo.jpg',
                'system/logo.jpeg',
                'system/logo.webp',
                'system/logo.svg',
                'settings/logo.png',
                'settings/logo.jpg',
                'settings/logo.jpeg',
                'settings/logo.webp',
                'settings/logo.svg',
                'logo.png',
                'logo.jpg',
                'logo.jpeg',
                'logo.webp',
                'logo.svg',
            ];

            foreach ($preferred as $path) {
                if ($disk->exists($path)) {
                    return $disk->url($path);
                }
            }

            foreach ($disk->allFiles() as $path) {
                $lowerPath = strtolower($path);
                $basename = strtolower(pathinfo($path, PATHINFO_FILENAME));
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                if (! in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'svg'], true)) {
                    continue;
                }

                $inBrandFolder = str_starts_with($lowerPath, 'branding/')
                    || str_starts_with($lowerPath, 'system/')
                    || str_starts_with($lowerPath, 'settings/')
                    || str_contains($lowerPath, '/branding/')
                    || str_contains($lowerPath, '/system/')
                    || str_contains($lowerPath, '/settings/');

                if (
                    $inBrandFolder
                    || $basename === 'logo'
                    || str_contains($basename, 'system-logo')
                    || str_contains($basename, 'platform-logo')
                    || str_contains($basename, 'brand-logo')
                ) {
                    return $disk->url($path);
                }
            }

            return null;
        });
    }
}
