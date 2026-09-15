<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,CourseQuiz,QuizQuestion}; use Illuminate\Http\Request;
class QuizController extends Controller {
 public function index(Course $course){return view('admin.quizzes.index',['course'=>$course,'quizzes'=>$course->hasMany(CourseQuiz::class)->with('questions')->get()]);}
 public function store(Request $r,Course $course){$d=$r->validate(['title'=>'required|string|max:190','pass_mark'=>'required|integer|min:1|max:100','max_attempts'=>'nullable|integer|min:1|max:20','is_published'=>'sometimes|boolean']);$course->hasMany(CourseQuiz::class)->create([...$d,'is_published'=>$r->boolean('is_published')]);return back()->with('success','Quiz created.');}
 public function storeQuestion(Request $r,CourseQuiz $quiz){$d=$r->validate(['question'=>'required|string','type'=>'required|in:single_choice,true_false,text','options'=>'nullable|array','correct_answer'=>'required','points'=>'required|integer|min:1|max:100','position'=>'nullable|integer|min:1']);$answer=is_array($d['correct_answer'])?$d['correct_answer']:[$d['correct_answer']];$quiz->questions()->create([...$d,'correct_answer'=>$answer,'position'=>$d['position']??($quiz->questions()->max('position')+1)]);return back()->with('success','Question added.');}
 public function destroy(CourseQuiz $quiz){$quiz->delete();return back()->with('success','Quiz deleted.');}
 public function destroyQuestion(CourseQuiz $quiz,QuizQuestion $question){abort_unless($question->course_quiz_id===$quiz->id,404);$question->delete();return back()->with('success','Question deleted.');}
}
