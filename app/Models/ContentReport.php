<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContentReport extends Model { protected $guarded=[]; protected $casts=['reviewed_at'=>'datetime']; public function reporter(){return $this->belongsTo(User::class,'reporter_id');} public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');} }
