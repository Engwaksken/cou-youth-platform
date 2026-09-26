<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCmsModuleAccess
{
    /** @var list<string> */
    private const FULL_ACCESS_ROLES = [
        'super_admin',
        'provincial_admin',
        'diocesan_admin',
        'archdeaconry_admin',
        'parish_admin',
        'local_church_admin',
    ];

    /** @var array<string, list<string>> */
    private const ROLE_ROUTE_PATTERNS = [
        'content_manager' => [
            'admin.dashboard',
            'admin.content.*',
            'admin.courses.*',
            'admin.quizzes.*',
            'admin.media.*',
            'admin.online-services.*',
            'admin.notifications.*',
            'admin.page-content.*',
            'admin.bulk.*',
        ],
        'events_manager' => [
            'admin.dashboard',
            'admin.events.*',
        ],
        'safeguarding_officer' => [
            'admin.dashboard',
            'admin.guardian-consents.*',
            'admin.prayer.*',
            'admin.moderation.*',
            'admin.comments.*',
        ],
        'finance_admin' => [
            'admin.dashboard',
            'admin.work-plans.*',
            'admin.annual-themes.*',
            'admin.calendar.*',
            'admin.donations.*',
            'admin.payment-gateways.*',
            'admin.membership-fees.*',
            'admin.financial-reports.*',
            'admin.reports.*',
        ],
        'donations_manager' => [
            'admin.dashboard',
            'admin.donations.*',
            'admin.payment-gateways.*',
            'admin.financial-reports.*',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $roles = $user->organisationRoles()
            ->where('is_active', true)
            ->get(['role', 'permissions']);

        if ($roles->contains(fn ($assignment): bool => in_array($assignment->role, self::FULL_ACCESS_ROLES, true))) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '') {
            abort(403, 'You do not have permission to access this CMS module.');
        }

        foreach ($roles as $assignment) {
            if ($this->matchesAny($routeName, self::ROLE_ROUTE_PATTERNS[$assignment->role] ?? [])) {
                return $next($request);
            }

            $permissions = is_array($assignment->permissions) ? $assignment->permissions : [];
            if ($this->matchesAny($routeName, $permissions)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this CMS module.');
    }

    /** @param array<int, mixed> $patterns */
    private function matchesAny(string $routeName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (! is_string($pattern) || $pattern === '') {
                continue;
            }

            if ($pattern === '*' || Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
