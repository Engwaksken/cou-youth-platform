<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CourseQuiz extends Model { protected $guarded=[]; protected $casts=['is_published'=>'boolean']; public function course(){return $this->belongsTo(Course::class);} public function questions(){return $this->hasMany(QuizQuestion::class)->orderBy('position');} public function attempts(){return $this->hasMany(QuizAttempt::class);} }
