<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Lesson extends Model {protected $guarded=[]; protected $casts=['is_published'=>'boolean']; public function course(){return $this->belongsTo(Course::class);} }