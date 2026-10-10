<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function view(User $actor, User $user): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function update(User $actor, User $user): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function delete(User $actor, User $user): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    private function isActiveSuperAdmin(User $actor): bool
    {
        return $actor->organisationRoles()
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->exists();
    }
}
