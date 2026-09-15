<?php
namespace App\Services\Access;
use App\Models\OrganisationUnit;
use Illuminate\Support\Collection;
class HierarchyScopeService {
 public function allowedUnitIds($user): Collection {
  if (!$user) return collect();
  if (method_exists($user,'hasRole') && $user->hasRole('super_admin')) return OrganisationUnit::pluck('id');
  $direct = collect($user->organisationRoles ?? [])->pluck('organisation_unit_id')->filter()->unique()->values();
  $all=$direct; $frontier=$direct;
  while($frontier->isNotEmpty()) { $children=OrganisationUnit::whereIn('parent_id',$frontier)->pluck('id'); $new=$children->diff($all); if($new->isEmpty()) break; $all=$all->merge($new)->unique(); $frontier=$new; }
  return $all->values();
 }
 public function canManage($user, ?int $unitId): bool { return $unitId===null ? false : $this->allowedUnitIds($user)->contains($unitId); }
}
