<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Support\Facades\DB;

trait GuardsSuperAdminMutations
{
    /** Serialize super-admin mutations across both row-locking databases and SQLite. */
    private function lockSuperAdminMutationSet(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite has no FOR UPDATE. Writing the database header obtains its
            // writer lock even when users is empty, without requiring a sentinel row.
            $version = (int) DB::selectOne('PRAGMA user_version')->user_version;
            DB::statement("PRAGMA user_version = {$version}");

            return;
        }

        User::query()->orderBy('id')->lockForUpdate()->get(['id']);
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
