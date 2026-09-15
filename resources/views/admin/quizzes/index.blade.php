@extends('admin.layout')
@section('content')
<div class="container-fluid">
 <h1>{{ $course->title }} – Quizzes</h1>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 <form method="POST" action="{{ route('admin.courses.quizzes.store',$course) }}" class="row g-2 mb-4">@csrf
  <div class="col-md-5"><input class="form-control" name="title" placeholder="Quiz title" required></div>
  <div class="col-md-2"><input class="form-control" name="pass_mark" type="number" min="1" max="100" value="70" required></div>
  <div class="col-md-2"><input class="form-control" name="max_attempts" type="number" min="1" max="20" placeholder="Attempts"></div>
  <div class="col-md-2 form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" id="published"><label class="form-check-label" for="published">Published</label></div>
  <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div>
 </form>
 @foreach($quizzes as $quiz)
 <div class="card mb-3"><div class="card-body"><div class="d-flex justify-content-between"><div><h5>{{ $quiz->title }}</h5><small>Pass {{ $quiz->pass_mark }}% · {{ $quiz->is_published?'Published':'Draft' }}</small></div><form method="POST" action="{{ route('admin.quizzes.destroy',$quiz) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></div>
 <hr><form method="POST" action="{{ route('admin.quizzes.questions.store',$quiz) }}" class="row g-2">@csrf
  <div class="col-md-5"><input name="question" class="form-control" placeholder="Question" required></div>
  <div class="col-md-2"><select name="type" class="form-select"><option value="single_choice">Single choice</option><option value="true_false">True/False</option><option value="text">Text</option></select></div>
  <div class="col-md-2"><input name="correct_answer" class="form-control" placeholder="Correct answer" required></div>
  <div class="col-md-1"><input name="points" type="number" class="form-control" value="1" min="1"></div>
  <div class="col-md-2"><button class="btn btn-secondary w-100">Add question</button></div>
 </form>
 <div class="mt-3">@forelse($quiz->questions as $question)<div class="border rounded p-2 mb-2 d-flex justify-content-between"><span>{{ $question->position }}. {{ $question->question }}</span><form method="POST" action="{{ route('admin.quizzes.questions.destroy',[$quiz,$question]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger">Delete</button></form></div>@empty<p class="text-muted">No questions yet.</p>@endforelse</div>
 </div></div>
 @endforeach
</div>
@endsection
