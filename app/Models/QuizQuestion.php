<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuizQuestion extends Model { protected $guarded=[]; protected $casts=['options'=>'array','correct_answer'=>'array']; public function quiz(){return $this->belongsTo(CourseQuiz::class,'course_quiz_id');} }
