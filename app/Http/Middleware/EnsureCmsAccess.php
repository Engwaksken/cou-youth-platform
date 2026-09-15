<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UserOrganisationRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowedRoles = [
            'super_admin',
            'provincial_admin',
            'diocesan_admin',
            'archdeaconry_admin',
            'parish_admin',
            'local_church_admin',
            'content_manager',
            'events_manager',
            'safeguarding_officer',
            'finance_admin',
            'donations_manager',
        ];

        $hasAccess = UserOrganisationRole::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereIn('role', $allowedRoles)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have permission to access the CMS.');
        }

        return $next($request);
    }
}