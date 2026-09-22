<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class EventRegistration extends Model { protected $fillable=['event_id','user_id','name','phone','email','status','payment_status']; }
