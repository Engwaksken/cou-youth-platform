<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{CourseQuiz,QuizAttempt};
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Request $request, CourseQuiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $attempts = QuizAttempt::where('course_quiz_id',$quiz->id)->where('user_id',$request->user()->id)->count();
        return response()->json([
            'quiz' => $quiz->load(['questions'=>fn($q)=>$q->select('id','course_quiz_id','question','type','options','points','position')]),
            'attempts_used' => $attempts,
            'attempts_remaining' => $quiz->max_attempts ? max(0,$quiz->max_attempts-$attempts) : null,
        ]);
    }

    public function submit(Request $request, CourseQuiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $used = QuizAttempt::where('course_quiz_id',$quiz->id)->where('user_id',$request->user()->id)->count();
        abort_if($quiz->max_attempts && $used >= $quiz->max_attempts, 422, 'Maximum quiz attempts reached.');
        $data = $request->validate(['answers'=>'required|array']);
        $questions = $quiz->questions()->get();
        $total = max(1,(int)$questions->sum('points'));
        $score = 0;
        foreach ($questions as $question) {
            $given = $data['answers'][(string)$question->id] ?? $data['answers'][$question->id] ?? null;
            $expected = $question->correct_answer;
            if ($this->normalise($given) === $this->normalise($expected)) $score += (int)$question->points;
        }
        $percentage = (int)round(($score/$total)*100);
        $attempt = QuizAttempt::create([
            'course_quiz_id'=>$quiz->id,'user_id'=>$request->user()->id,'score'=>$score,
            'percentage'=>$percentage,'passed'=>$percentage >= $quiz->pass_mark,
            'answers'=>$data['answers'],'submitted_at'=>now(),
        ]);
        return response()->json(['message'=>$attempt->passed?'Quiz passed.':'Quiz submitted.','attempt'=>$attempt],201);
    }

    private function normalise(mixed $value): string
    {
        if (is_array($value)) { sort($value); return json_encode(array_values($value)); }
        return mb_strtolower(trim((string)$value));
    }
}
