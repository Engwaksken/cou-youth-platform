<?php

declare(strict_types=1);

namespace App\Services\Branding;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Site-wide look that admins can change from Settings > Appearance: colours,
 * fonts, overall style and the sidebar background. Applies to the public site,
 * the youth portal and the admin area. Anything not set falls back to the
 * defaults below (and brand colours to config/branding.php).
 */
final class AppearanceService
{
    public const CACHE_KEY = 'platform_appearance';

    /** Google Fonts offered in the font pickers: label => CSS family spec for the fonts URL. */
    public const FONTS = [
        'Bricolage Grotesque' => 'Bricolage+Grotesque:opsz,wght@12..96,500..800',
        'Figtree' => 'Figtree:ital,wght@0,400..800;1,400..600',
        'Poppins' => 'Poppins:wght@400;500;600;700;800',
        'Nunito' => 'Nunito:wght@400..800',
        'Montserrat' => 'Montserrat:wght@400..800',
        'Outfit' => 'Outfit:wght@400..800',
        'Lexend' => 'Lexend:wght@400..800',
        'DM Sans' => 'DM+Sans:wght@400..800',
        'Inter' => 'Inter:wght@400..800',
        'Fraunces' => 'Fraunces:opsz,wght@9..144,500..800',
        'Playfair Display' => 'Playfair+Display:wght@500..800',
    ];

    /** Overall card and button style: key => [label, description]. */
    public const STYLES = [
        'playful' => ['Playful', 'Bold outlines and solid offset shadows. Fun and hand-made.'],
        'soft' => ['Soft', 'Rounded corners, gentle shadows, no outlines. Calm and friendly.'],
        'clean' => ['Clean', 'Flat, light borders, smaller corners. Simple and modern.'],
    ];

    public const DEFAULTS = [
        'accent_color' => '#FFC23D',
        'background_color' => '#FBF6EE',
        'sidebar_color' => null,
        'heading_font' => 'Bricolage Grotesque',
        'body_font' => 'Figtree',
        'theme_style' => 'playful',
    ];

    /** Setting keys this service owns (all stored in site_settings). */
    public const KEYS = ['primary_color', 'secondary_color', 'accent_color', 'background_color', 'sidebar_color', 'heading_font', 'body_font', 'theme_style'];

    /**
     * @return array{primary_color:string,secondary_color:string,accent_color:string,background_color:string,sidebar_color:?string,sidebar_text:?string,heading_font:string,body_font:string,theme_style:string,fonts_url:string}
     */
    public function data(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), fn (): array => $this->resolve());
        } catch (\Throwable) {
            return $this->resolve();
        }
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('platform_branding_logo_url');
    }

    public static function isHexColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /** Dark text on light backgrounds, white text on dark ones (WCAG relative luminance). */
    public static function readableTextOn(string $hex): string
    {
        $channels = array_map(function (string $pair): float {
            $c = hexdec($pair) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        return $luminance > 0.4 ? '#1D1530' : '#FFFFFF';
    }

    private function resolve(): array
    {
        $stored = [];
        foreach (self::KEYS as $key) {
            $stored[$key] = SiteSetting::get($key);
        }

        $color = fn (string $key, ?string $fallback): ?string => self::isHexColor($stored[$key]) ? strtoupper($stored[$key]) : $fallback;
        $font = fn (string $key): string => array_key_exists((string) $stored[$key], self::FONTS) ? (string) $stored[$key] : self::DEFAULTS[$key];

        $headingFont = $font('heading_font');
        $bodyFont = $font('body_font');
        $sidebar = $color('sidebar_color', null);

        $families = array_unique([self::FONTS[$headingFont], self::FONTS[$bodyFont]]);

        return [
            'primary_color' => $color('primary_color', self::isHexColor(config('branding.primary_color')) ? (string) config('branding.primary_color') : '#4B2E83'),
            'secondary_color' => $color('secondary_color', self::isHexColor(config('branding.secondary_color')) ? (string) config('branding.secondary_color') : '#204F78'),
            'accent_color' => $color('accent_color', self::DEFAULTS['accent_color']),
            'background_color' => $color('background_color', self::DEFAULTS['background_color']),
            'sidebar_color' => $sidebar,
            'sidebar_text' => $sidebar ? self::readableTextOn($sidebar) : null,
            'heading_font' => $headingFont,
            'body_font' => $bodyFont,
            'theme_style' => array_key_exists((string) $stored['theme_style'], self::STYLES) ? (string) $stored['theme_style'] : self::DEFAULTS['theme_style'],
            'fonts_url' => 'https://fonts.googleapis.com/css2?family='.implode('&family=', $families).'&display=swap',
        ];
    }
}
