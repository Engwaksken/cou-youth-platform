<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Audit\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AuditCmsMutations
{
    public function __construct(private readonly AuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->user()
            || ! $request->is('admin', 'admin/*')
            || ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            || $response->getStatusCode() >= 400) {
            return $response;
        }

        try {
            $routeName = (string) optional($request->route())->getName();
            $module = $routeName !== '' ? $routeName : 'admin';

            $this->audit->log(
                strtolower($request->method()),
                $module,
                null,
                null,
                [
                    'route' => $routeName,
                    'path' => '/'.ltrim($request->path(), '/'),
                    'status' => $response->getStatusCode(),
                ],
            );
        } catch (Throwable $exception) {
            // Audit logging must never turn a successful CMS mutation into a 500.
            report($exception);
        }

        return $response;
    }
}
