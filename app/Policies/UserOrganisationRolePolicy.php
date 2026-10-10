<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserOrganisationRole;

class UserOrganisationRolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function view(User $actor, UserOrganisationRole $assignment): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function update(User $actor, UserOrganisationRole $assignment): bool
    {
        return $this->isActiveSuperAdmin($actor);
    }

    public function delete(User $actor, UserOrganisationRole $assignment): bool
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
