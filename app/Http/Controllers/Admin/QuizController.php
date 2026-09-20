<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseQuiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Course $course)
    {
        return view('admin.quizzes.index', [
            'course' => $course,
            'quizzes' => $course->hasMany(CourseQuiz::class)->with('questions')->get(),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $data = $this->quizData($request);
        $course->hasMany(CourseQuiz::class)->create([...$data, 'is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Quiz created.');
    }

    public function update(Request $request, CourseQuiz $quiz)
    {
        $data = $this->quizData($request);
        $quiz->update([...$data, 'is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Quiz updated.');
    }

    public function storeQuestion(Request $request, CourseQuiz $quiz)
    {
        $data = $this->questionData($request);
        $quiz->questions()->create([
            ...$data,
            'correct_answer' => $this->normaliseAnswer($data['correct_answer']),
            'position' => $data['position'] ?? ($quiz->questions()->max('position') + 1),
        ]);
        return back()->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, CourseQuiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->course_quiz_id === $quiz->id, 404);
        $data = $this->questionData($request);
        $question->update([
            ...$data,
            'correct_answer' => $this->normaliseAnswer($data['correct_answer']),
            'position' => $data['position'] ?? $question->position,
        ]);
        return back()->with('success', 'Question updated.');
    }

    public function destroy(CourseQuiz $quiz)
    {
        $quiz->delete();
        return back()->with('success', 'Quiz deleted.');
    }

    public function destroyQuestion(CourseQuiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->course_quiz_id === $quiz->id, 404);
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    private function quizData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:190',
            'pass_mark' => 'required|integer|min:1|max:100',
            'max_attempts' => 'nullable|integer|min:1|max:20',
            'is_published' => 'sometimes|boolean',
        ]);
    }

    private function questionData(Request $request): array
    {
        return $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single_choice,true_false,text',
            'options' => 'nullable|array',
            'correct_answer' => 'required',
            'points' => 'required|integer|min:1|max:100',
            'position' => 'nullable|integer|min:1',
        ]);
    }

    private function normaliseAnswer(mixed $answer): array
    {
        return is_array($answer) ? $answer : [(string) $answer];
    }
}
