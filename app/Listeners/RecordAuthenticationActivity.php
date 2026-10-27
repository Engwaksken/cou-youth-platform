<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Audit\AuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Throwable;

final class RecordAuthenticationActivity
{
    public function __construct(private readonly AuditService $audit) {}

    public function handleLogin(Login $event): void
    {
        $this->record('login', $event->user?->getAuthIdentifier(), [
            'guard' => $event->guard,
            'remembered' => $event->remember,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        $this->record('logout', $event->user?->getAuthIdentifier(), ['guard' => $event->guard]);
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        // Only the attempted identity is kept; never the submitted password.
        $this->record('login_failed', $event->user?->getAuthIdentifier(), [
            'guard' => $event->guard,
            'email' => is_string($email) ? strtolower(trim($email)) : null,
        ]);
    }

    /** @param array<string, mixed> $details */
    private function record(string $action, mixed $userId, array $details): void
    {
        try {
            $this->audit->log($action, 'auth', null, null, $details, is_numeric($userId) ? (int) $userId : null);
        } catch (Throwable $exception) {
            // Activity logging must never block signing in or out.
            report($exception);
        }
    }
}
