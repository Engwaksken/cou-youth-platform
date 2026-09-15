<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class GuardianConsent extends Model { public function user(){ return $this->belongsTo(User::class); } public function verifier(){ return $this->belongsTo(User::class,'verified_by'); } protected $fillable=['user_id','guardian_name','relationship','guardian_phone','guardian_email','status','consented_at','verified_by','verified_at']; protected $casts=['consented_at'=>'datetime','verified_at'=>'datetime']; }
