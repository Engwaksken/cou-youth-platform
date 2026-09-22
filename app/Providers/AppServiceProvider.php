<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
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
}
