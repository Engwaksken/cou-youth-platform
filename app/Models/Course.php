<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Course extends Model {protected $guarded=[]; protected $casts=['is_published'=>'boolean']; public function lessons(){return $this->hasMany(Lesson::class)->orderBy('position');} public function enrolments(){return $this->hasMany(CourseEnrolment::class);} }