<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\PageText as PageTextModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

/**
 * Editable wording for the public pages.
 *
 * Defaults live in config/page_content.php; super admins override them from
 * Admin > Page Content. Views print a value with @pageText('home.hero_title').
 * Registered as a scoped binding, so overrides are loaded once per request.
 */
final class PageText
{
    /** @var array<string, array<string, string>> */
    private array $loaded = [];

    /** @return array<string, array{label: string, fields: array<string, array{label: string, type?: string, default: string, group?: string}>}> */
    public static function pages(): array
    {
        return config('page_content.pages', []);
    }

    /** @return array{label: string, type: string, default: string, group?: string} */
    public static function field(string $page, string $key): array
    {
        $field = self::pages()[$page]['fields'][$key] ?? null;
        if ($field === null) {
            throw new InvalidArgumentException("Unknown page text [{$page}.{$key}].");
        }

        return $field + ['type' => 'text', 'default' => ''];
    }

    public static function get(string $path): string
    {
        [$page, $key] = self::split($path);
        $field = self::field($page, $key);

        return app(self::class)->overrides($page)[$key] ?? $field['default'];
    }

    /** Escaped output; multi-line fields keep their line breaks. */
    public static function render(string $path): HtmlString
    {
        [$page, $key] = self::split($path);
        $value = e(self::get($path));

        return new HtmlString(self::field($page, $key)['type'] === 'textarea' ? nl2br($value, false) : $value);
    }

    /** @return array<string, string> */
    public function overrides(string $page): array
    {
        if (! array_key_exists($page, $this->loaded)) {
            try {
                $this->loaded[$page] = Schema::hasTable('page_texts')
                    ? PageTextModel::query()->where('page', $page)->pluck('value', 'key')->all()
                    : [];
            } catch (\Throwable) {
                $this->loaded[$page] = [];
            }
        }

        return $this->loaded[$page];
    }

    public function forget(string $page): void
    {
        unset($this->loaded[$page]);
    }

    /** @return array{0: string, 1: string} */
    private static function split(string $path): array
    {
        $parts = explode('.', $path, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Page text path [{$path}] must look like page.key.");
        }

        return $parts;
    }
}
