@extends('public.layout')
@section('title', $course->title.' | COU Youth')
@section('content')
@php
    $enrolment = null;
    $completedLessonIds = [];
    if (auth()->check()) {
        $enrolment = \App\Models\CourseEnrolment::query()
            ->where('course_id', $course->id)
            ->where('user_id', auth()->id())
            ->first();
        $completedLessonIds = \Illuminate\Support\Facades\DB::table('lesson_progress')
            ->where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
@endphp
<nav class="detail-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('public.courses') }}"><i class="fas fa-arrow-left"></i> Back to Courses</a></nav>
<section class="course-detail-layout">
<article>
@if(!empty($course->image_path))<img class="course-hero-image" src="{{ asset('storage/'.$course->image_path) }}" alt="{{ $course->title }}">@endif
<div class="course-heading"><span class="badge">{{ ucwords(str_replace('_',' ',$course->age_category ?: 'All ages')) }}</span><h1>{{ $course->title }}</h1>@if(!empty($course->description))<p class="course-lead">{{ $course->description }}</p>@endif</div>
<section class="card course-section"><div class="section-title"><span class="section-icon"><i class="fas fa-list-check"></i></span><div><h2>Course lessons</h2><p class="muted">{{ $course->lessons->count() }} {{ $course->lessons->count() === 1 ? 'lesson' : 'lessons' }}</p></div></div>
@if($course->lessons->isNotEmpty())
<div class="lesson-list">
@foreach($course->lessons as $index=>$lesson)
@php($completed=in_array((int)$lesson->id,$completedLessonIds,true))
<div class="lesson-row {{ $completed ? 'lesson-complete' : '' }}">
<span class="lesson-number">{{ $completed ? '✓' : $index+1 }}</span>
<div class="lesson-body"><strong>{{ $lesson->title ?? 'Lesson '.($index+1) }}</strong>@if(!empty($lesson->body))<p class="muted">{{ \Illuminate\Support\Str::limit(strip_tags($lesson->body),180) }}</p>@endif @if(!empty($lesson->media_url))<a href="{{ $lesson->media_url }}" target="_blank" rel="noopener noreferrer" class="lesson-media"><i class="fas fa-play"></i> Open lesson media</a>@endif</div>
@auth
@if($enrolment && !$completed && $lesson->is_published)
<form method="POST" action="{{ route('youth.lessons.complete',$lesson) }}">@csrf<button class="btn lesson-action" type="submit"><i class="fas fa-check"></i> Mark complete</button></form>
@elseif($completed)<span class="complete-label"><i class="fas fa-circle-check"></i> Completed</span>@endif
@endauth
</div>
@endforeach
</div>
@else<p class="muted">Lessons for this course will be published soon.</p>@endif
</section>
</article>
<aside class="course-sidebar"><div class="card course-action"><span class="action-icon"><i class="fas fa-book-bible"></i></span><h2>{{ $enrolment ? 'Your progress' : 'Start learning' }}</h2>
@auth
@if($enrolment)
<div class="progress-value">{{ (int)$enrolment->progress_percent }}%</div><div class="progress-track"><span style="width:{{ min(100,(int)$enrolment->progress_percent) }}%"></span></div><p class="muted">{{ $enrolment->completed_at ? 'Course completed. Your certificate is available in My Certificates.' : 'Complete the lessons above to update your course progress.' }}</p><a class="btn btn-wide" href="{{ route('youth.learning') }}">My learning</a>@if($enrolment->completed_at)<a class="btn btn-primary btn-wide" href="{{ route('youth.certificates') }}">View certificate</a>@endif
@else
<p class="muted">Enrol to track lesson completion and course progress.</p><form method="POST" action="{{ route('youth.courses.enrol',$course) }}">@csrf<button class="btn btn-primary btn-wide" type="submit">Enrol in course</button></form>
@endif
@else
<p class="muted">Sign in to access personalised course progress and participation.</p><a class="btn btn-primary btn-wide" href="{{ route('login') }}">Youth Login</a><a class="btn btn-wide" href="{{ route('register') }}">Create Account</a>
@endauth
</div></aside>
</section>
@if($relatedCourses->isNotEmpty())<section class="related-section"><span class="section-kicker">KEEP LEARNING</span><h2>More discipleship courses</h2><div class="grid">@foreach($relatedCourses as $related)<a class="card related-card" href="{{ route('public.courses.show',$related) }}"><span class="related-icon"><i class="fas fa-book-bible"></i></span><h3>{{ $related->title }}</h3><p class="muted">{{ \Illuminate\Support\Str::limit($related->description ?: 'Discipleship course',120) }}</p><span class="detail-link">View course <i class="fas fa-arrow-right"></i></span></a>@endforeach</div></section>@endif
<style>
.card{border-radius:10px!important}.detail-breadcrumb{margin-bottom:20px}.detail-breadcrumb a{display:inline-flex;gap:8px;align-items:center;color:var(--primary);font-weight:800}.course-detail-layout{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(260px,.65fr);gap:24px;align-items:start}.course-hero-image{width:100%;max-height:440px;object-fit:cover;border-radius:10px;display:block;margin-bottom:22px}.course-heading{margin-bottom:22px}.course-heading h1{font-size:clamp(2rem,5vw,3.2rem);line-height:1.08;margin:10px 0}.course-lead{font-size:1.08rem;color:var(--muted);line-height:1.65}.course-section{padding:22px}.section-title{display:flex;gap:12px;align-items:center;margin-bottom:18px}.section-title h2,.section-title p{margin:0}.section-icon,.action-icon,.related-icon{width:46px;height:46px;min-width:46px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center}.lesson-list{display:grid;gap:10px}.lesson-row{display:flex;gap:12px;align-items:flex-start;padding:14px;border:1px solid var(--border);border-radius:10px;background:#faf9fc}.lesson-complete{border-left:4px solid var(--primary)}.lesson-number{width:34px;height:34px;min-width:34px;border-radius:8px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:900}.lesson-body{flex:1}.lesson-row p{margin:5px 0 0}.lesson-media{display:inline-flex;gap:6px;align-items:center;margin-top:8px;color:var(--primary);font-weight:800;font-size:.85rem}.lesson-action{white-space:nowrap}.complete-label{color:var(--primary);font-weight:800;font-size:.82rem;white-space:nowrap}.course-sidebar{position:sticky;top:92px}.course-action{text-align:center;padding:24px}.action-icon{margin:0 auto 14px;width:54px;height:54px}.course-action h2{margin:0 0 8px}.btn-wide{width:100%;margin-top:8px}.progress-value{font-size:2rem;font-weight:900;color:var(--primary);margin:12px 0 7px}.progress-track{height:9px;border-radius:999px;background:#e9e4ef;overflow:hidden}.progress-track span{display:block;height:100%;background:var(--primary);border-radius:999px}.related-section{margin-top:36px}.section-kicker{color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em}.related-card{display:block;color:inherit}.related-icon{margin-bottom:12px}.detail-link{color:var(--primary);font-weight:800}@media(max-width:850px){.course-detail-layout{grid-template-columns:1fr}.course-sidebar{position:static}.lesson-row{flex-wrap:wrap}.lesson-action{width:100%}}
</style>
@endsection