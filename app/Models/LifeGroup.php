<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LifeGroup extends Model { public function organisationUnit(){ return $this->belongsTo(OrganisationUnit::class); } protected $fillable=['organisation_unit_id','name','description','leader_user_id','member_limit','meeting_day','meeting_time','meeting_location','is_active']; protected $casts=['is_active'=>'boolean']; public function members(){return $this->hasMany(LifeGroupMember::class);} }
