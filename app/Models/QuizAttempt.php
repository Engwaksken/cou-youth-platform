<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuizAttempt extends Model { protected $guarded=[]; protected $casts=['answers'=>'array','passed'=>'boolean','submitted_at'=>'datetime']; public function quiz(){return $this->belongsTo(CourseQuiz::class,'course_quiz_id');} public function user(){return $this->belongsTo(User::class);} }
