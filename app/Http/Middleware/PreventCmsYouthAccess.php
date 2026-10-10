<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreventCmsYouthAccess
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasCmsAccess()) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();

        if (
            str_starts_with($routeName, 'youth.')
            || $routeName === 'youth-assistant.reply'
        ) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Administrator accounts cannot access the youth portal. Please use the administration dashboard.');
        }

        return $next($request);
    }
}
