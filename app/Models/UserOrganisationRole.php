<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserOrganisationRole extends Model { protected $fillable=['user_id','organisation_unit_id','role']; public function organisationUnit(){ return $this->belongsTo(OrganisationUnit::class); } public function user(){ return $this->belongsTo(User::class); } }
