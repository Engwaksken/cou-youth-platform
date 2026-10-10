<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrganisationRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'organisation_unit_id' => $this->organisation_unit_id, 'name' => $this->name, 'permissions' => $this->permissions ?? []];
    }
}
