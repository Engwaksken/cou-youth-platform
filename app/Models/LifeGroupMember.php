<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LifeGroupMember extends Model { protected $fillable=['life_group_id','user_id','role','status']; }
