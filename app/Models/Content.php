<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Content extends Model { protected $fillable=['type','organisation_unit_id','title','slug','summary','body','featured_image','file_path','external_url','target_age_categories','is_official','status','created_by','approved_by','published_at']; protected $casts=['target_age_categories'=>'array','is_official'=>'boolean','published_at'=>'datetime']; }
