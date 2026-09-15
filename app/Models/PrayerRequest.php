<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PrayerRequest extends Model {protected $guarded=[]; protected $casts=['requires_safeguarding_review'=>'boolean'];}