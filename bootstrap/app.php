<?php

use App\Http\Middleware\EnsureCmsAccess;
use App\Http\Middleware\EnsureCmsModuleAccess;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/youth-engagement.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'cms.access' => EnsureCmsAccess::class,
            'cms.module' => EnsureCmsModuleAccess::class,
        ]);

        $middleware->redirectGuestsTo('/login');

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            return $user && $user->hasCmsAccess() ? '/admin' : '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel's production exception renderer is used for web responses.
        // API controllers return intentionally sanitised JSON errors.
    })
    ->create();
