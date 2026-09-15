<?php
namespace App\Models\Concerns;
use App\Models\UserOrganisationRole;
trait HasChurchScope { public function organisationRoles(){ return $this->hasMany(UserOrganisationRole::class); } }
