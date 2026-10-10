<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserOrganisationRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'user_id' => $this->user_id, 'organisation_unit_id' => $this->organisation_unit_id, 'organisation_role_id' => $this->organisation_role_id, 'role' => $this->role, 'permissions' => $this->permissions ?? [], 'is_active' => $this->is_active];
    }
}
