<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();

        Route::middleware('web')->group(base_path('routes/youth-engagement.php'));

        View::composer('*', function ($view): void {
            try {
                $systemName = \App\Models\SiteSetting::get('system_name', 'Church of Uganda Youth Platform') ?: 'Church of Uganda Youth Platform';
                $logo = \App\Models\SiteSetting::get('logo');
                $favicon = \App\Models\SiteSetting::get('favicon');
            } catch (\Throwable $e) {
                $systemName = 'Church of Uganda Youth Platform';
                $logo = null;
                $favicon = null;
            }

            if (! is_string($logo) || $logo === '' || ! Storage::disk('public')->exists($logo)) {
                $logo = null;
            }

            if (! is_string($favicon) || $favicon === '' || ! Storage::disk('public')->exists($favicon)) {
                $favicon = null;
            }

            $view->with('system_name', $systemName);
            $view->with('logo', $logo);
            $view->with('favicon', $favicon);
        });
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('youth-login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($this->requestIdentity($request, 'email')));

        RateLimiter::for('admin-login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($this->requestIdentity($request, 'email')));

        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip()));

        RateLimiter::for('password-recovery', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($this->requestIdentity($request, 'email')));

        RateLimiter::for('otp-request', fn (Request $request): Limit => Limit::perMinute(3)
            ->by($this->requestIdentity($request, 'email')));

        RateLimiter::for('otp-verify', fn (Request $request): Limit => Limit::perMinute(10)
            ->by($this->requestIdentity($request, 'email')));
    }

    private function requestIdentity(Request $request, string $field): string
    {
        $value = strtolower(trim((string) $request->input($field, '')));

        return ($value !== '' ? $value : 'anonymous').'|'.$request->ip();
    }
}
