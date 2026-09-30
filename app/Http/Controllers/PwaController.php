<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\Branding\BrandingService;
use Illuminate\Http\Response;

final class PwaController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    public function manifest(): Response
    {
        $brand = $this->branding->data();

        $name = trim((string) SiteSetting::get('system_name', $brand['name'] ?? 'Church of Uganda Youth Platform'));
        $shortName = trim((string) ($brand['short_name'] ?? 'COU Youth Platform'));
        $theme = trim((string) ($brand['primary_color'] ?? '#4B2E83')) ?: '#4B2E83';
        $background = '#ffffff';

        $manifest = [
            'id' => '/',
            'name' => $name !== '' ? $name : 'Church of Uganda Youth Platform',
            'short_name' => $shortName !== '' ? $shortName : 'COU Youth',
            'description' => trim((string) ($brand['tagline'] ?? 'Connect, grow and serve.')),
            'start_url' => '/dashboard',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => $background,
            'theme_color' => $theme,
            'icons' => [
                [
                    'src' => route('pwa.icon', ['size' => 192]),
                    'sizes' => '192x192',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any',
                ],
                [
                    'src' => route('pwa.icon', ['size' => 512]),
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response(
            json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            200,
            [
                'Content-Type' => 'application/manifest+json; charset=UTF-8',
                'Cache-Control' => 'public, max-age=300',
            ],
        );
    }

    public function icon(int $size): Response
    {
        $size = in_array($size, [192, 512], true) ? $size : 192;
        $brand = $this->branding->data();

        $primary = trim((string) ($brand['primary_color'] ?? '#4B2E83')) ?: '#4B2E83';
        $logoUrl = $brand['logo_url'] ?? null;
        $shortName = trim((string) ($brand['short_name'] ?? 'COU Youth')) ?: 'COU Youth';

        $safePrimary = htmlspecialchars($primary, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $safeName = htmlspecialchars($shortName, ENT_QUOTES | ENT_XML1, 'UTF-8');

        if (is_string($logoUrl) && $logoUrl !== '') {
            $safeLogo = htmlspecialchars($logoUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $inner = '<rect x="20%" y="20%" width="60%" height="60%" rx="14%" fill="#ffffff"/>'
                .'<image href="'.$safeLogo.'" x="24%" y="24%" width="52%" height="52%" preserveAspectRatio="xMidYMid meet"/>';
        } else {
            $fontSize = $size === 512 ? 66 : 26;
            $inner = '<text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="'.$fontSize.'" font-weight="700">'.$safeName.'</text>';
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
            .'<rect width="100%" height="100%" rx="20%" fill="'.$safePrimary.'"/>'
            .$inner
            .'</svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
