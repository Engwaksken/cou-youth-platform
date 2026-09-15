<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlatformNotification extends Model {
 protected $fillable=['title','body','channel','organisation_unit_id','age_category','action_url','scheduled_at','sent_at','created_by','is_active'];
 protected $casts=['scheduled_at'=>'datetime','sent_at'=>'datetime','is_active'=>'boolean'];
 public function organisationUnit(){ return $this->belongsTo(OrganisationUnit::class); }
 public function receipts(){ return $this->hasMany(PlatformNotificationReceipt::class); }
}
