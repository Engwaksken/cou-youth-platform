<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlatformNotificationReceipt extends Model { public function notification(){ return $this->belongsTo(PlatformNotification::class,'platform_notification_id'); } protected $fillable=['platform_notification_id','user_id','delivered_at','read_at']; protected $casts=['delivered_at'=>'datetime','read_at'=>'datetime']; }
