<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationPreference extends Model { protected $guarded=[]; protected $casts=['in_app'=>'boolean','push'=>'boolean','email'=>'boolean','events'=>'boolean','discipleship'=>'boolean','opportunities'=>'boolean','donations'=>'boolean','life_groups'=>'boolean']; }
