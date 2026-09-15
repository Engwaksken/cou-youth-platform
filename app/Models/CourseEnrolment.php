<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CourseEnrolment extends Model {protected $guarded=[]; protected $casts=['completed_at'=>'datetime']; public function course(){return $this->belongsTo(Course::class);} }