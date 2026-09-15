<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class YouthProfile extends Model { protected $fillable=['user_id','date_of_birth','age_category','organisation_unit_id','school_institution','interests','talents','skills','ministry_interests','profile_photo','profile_public','safeguarding_verified']; protected $casts=['date_of_birth'=>'date','interests'=>'array','talents'=>'array','skills'=>'array','ministry_interests'=>'array','profile_public'=>'boolean','safeguarding_verified'=>'boolean']; public function organisationUnit(){return $this->belongsTo(OrganisationUnit::class);} }
