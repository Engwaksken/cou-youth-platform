<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait GuardsSuperAdminMutations
{
    /** @var resource|null */
    private $rbacMutationLock = null;
    /** Serialize super-admin mutations across both row-locking databases and SQLite. */
    private function lockSuperAdminMutationSet(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite's deferred transactions and row locks do not serialize readers
            // that later become writers. Use an OS lock keyed to the DB file; this
            // works even before the first user row exists. Hold it through the
            // surrounding transaction (released by unlockSuperAdminMutationSet).
            $database = DB::connection()->getDatabaseName();
            // Laravel may be configured with a relative SQLite path, and
            // aliases/symlinks to the same file must coordinate on one lock.
            if ($database !== ':memory:' && $database !== '') {
                $database = realpath($database) ?: (str_starts_with($database, DIRECTORY_SEPARATOR)
                    ? $database
                    : getcwd().DIRECTORY_SEPARATOR.$database);
                $database = str_replace('\\', '/', $database);
                if (DIRECTORY_SEPARATOR === '\\') {
                    $database = strtolower($database);
                }
            }
            $key = hash('sha256', $database);
            $handle = fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'rbac-'.$key.'.lock', 'c');
            if ($handle === false || ! flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to acquire the RBAC mutation lock.');
            }
            $this->rbacMutationLock = $handle;

            return;
        }

        User::query()->orderBy('id')->lockForUpdate()->get(['id']);
    }

    private function unlockSuperAdminMutationSet(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (is_resource($this->rbacMutationLock)) {
            flock($this->rbacMutationLock, LOCK_UN);
            fclose($this->rbacMutationLock);
            $this->rbacMutationLock = null;
        }
    }

    private function withinSuperAdminMutationLock(callable $callback): mixed
    {
        try {
            return DB::transaction(function () use ($callback): mixed {
                $this->lockSuperAdminMutationSet();

                return $callback();
            });
        } finally {
            // Keep the SQLite file lock until the transaction has committed or
            // rolled back. This is a single-host coordination mechanism only;
            // it does not provide distributed or multi-host locking.
            $this->unlockSuperAdminMutationSet();
        }
    }

    private function guardLastSuperAdminAssignment(UserOrganisationRole $assignment, array $changes): void
    {
        if ($assignment->role !== 'super_admin' || ! $assignment->is_active
            || (($changes['role'] ?? 'super_admin') === 'super_admin' && ($changes['is_active'] ?? true))) {
            return;
        }

        $count = UserOrganisationRole::query()->where('role', 'super_admin')->where('is_active', true)->distinct('user_id')->count('user_id');
        $hasOtherAssignment = UserOrganisationRole::query()->where('user_id', $assignment->user_id)->where('role', 'super_admin')->where('is_active', true)->where('id', '<>', $assignment->id)->exists();
        abort_if($count <= 1 && ! $hasOtherAssignment, 422, 'The last active super administrator cannot be removed.');
    }

    private function guardLastSuperAdminUserDeletion(User $user): void
    {
        $isActiveSuperAdmin = $user->organisationRoles()->where('role', 'super_admin')->where('is_active', true)->exists();
        $hasOtherActiveSuperAdmin = UserOrganisationRole::query()
            ->where('role', 'super_admin')->where('is_active', true)->where('user_id', '<>', $user->id)
            ->distinct('user_id')->exists();

        abort_if($isActiveSuperAdmin && ! $hasOtherActiveSuperAdmin, 422, 'The last active super administrator cannot be removed.');
    }
}
