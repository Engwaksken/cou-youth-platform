<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ChurchLocation extends Model {protected $guarded=[]; public function organisationUnit(){return $this->belongsTo(OrganisationUnit::class);} }