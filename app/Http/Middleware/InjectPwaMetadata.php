<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Services\Branding\BrandingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InjectPwaMetadata
{
    public function __construct(private readonly BrandingService $branding) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        /*
         * Illuminate\Http\Response keeps the original controller result so
         * Laravel can expose view data to tests and other framework features.
         * Calling setContent() replaces that original value with the rendered
         * HTML string. Preserve it before injecting the PWA markup, then put it
         * back afterwards when the response supports Laravel's original
         * response contract.
         */
        $original = method_exists($response, 'getOriginalContent')
            ? $response->getOriginalContent()
            : null;

        $contentType = (string) $response->headers->get('Content-Type', '');
        $content = $response->getContent();

        if (
            ! is_string($content)
            || $content === ''
            || ! str_contains(strtolower($contentType), 'text/html')
            || ! str_contains(strtolower($content), '</head>')
        ) {
            return $response;
        }

        $brand = $this->branding->data();
        $themeColor = trim((string) ($brand['primary_color'] ?? '#4B2E83')) ?: '#4B2E83';
        $systemName = trim((string) SiteSetting::get('system_name', $brand['short_name'] ?? 'COU Youth Platform'));
        $systemName = $systemName !== '' ? $systemName : 'COU Youth Platform';

        $head = implode("\n", [
            '<link rel="manifest" href="'.e(route('pwa.manifest')).'">',
            '<meta name="theme-color" content="'.e($themeColor).'">',
            '<meta name="application-name" content="'.e($systemName).'">',
            '<meta name="apple-mobile-web-app-capable" content="yes">',
            '<meta name="apple-mobile-web-app-status-bar-style" content="default">',
            '<meta name="apple-mobile-web-app-title" content="'.e($systemName).'">',
            '<link rel="apple-touch-icon" href="'.e(route('pwa.icon', ['size' => 192])).'">',
        ]);

        $registration = <<<'HTML'
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
    });
}
</script>
HTML;

        $content = preg_replace('/<\/head>/i', $head."\n</head>", $content, 1) ?? $content;

        if (str_contains(strtolower($content), '</body>')) {
            $content = preg_replace('/<\/body>/i', $registration."\n</body>", $content, 1) ?? $content;
        } else {
            $content .= $registration;
        }

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        if ($original !== null && property_exists($response, 'original')) {
            $response->original = $original;
        }

        return $response;
    }
}
