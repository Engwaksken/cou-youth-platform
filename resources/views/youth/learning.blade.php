@extends('youth.layout')
@section('title', 'My Learning | COU Youth')
@section('youth_content')
<section class="hero"><span class="section-kicker">LEARNING</span><h1>My learning</h1><p>Review your enrolled courses and discover published learning that matches your youth profile.</p></section>

<section class="learning-section">
<div class="section-head"><div><span class="section-kicker">ENROLLED</span><h2>Your courses</h2></div><a class="btn" href="{{ route('public.courses') }}">Browse catalogue</a></div>
<div class="course-grid">
@forelse($enrolments as $enrolment)
<article class="card course-card">
<div class="course-icon"><i class="fas fa-book-open"></i></div>
<h3>{{ $enrolment->course?->title ?? 'Course unavailable' }}</h3>
@if($enrolment->course?->description)<p class="muted">{{ \Illuminate\Support\Str::limit($enrolment->course->description,140) }}</p>@endif
<div class="progress-wrap" aria-label="Course progress {{ $enrolment->progress_percent }} percent"><div class="progress-bar"><span style="width:{{ min(100, max(0, (int) $enrolment->progress_percent)) }}%"></span></div><strong>{{ (int) $enrolment->progress_percent }}%</strong></div>
<div class="course-meta"><span class="badge">{{ $enrolment->completed_at ? 'Completed' : ((int) $enrolment->progress_percent > 0 ? 'In progress' : 'Enrolled') }}</span>@if($enrolment->completed_at)<span><i class="fas fa-circle-check"></i> {{ $enrolment->completed_at->format('d M Y') }}</span>@endif</div>
@if($enrolment->course)<a class="btn btn-primary" href="{{ route('public.courses.show',$enrolment->course) }}">{{ $enrolment->completed_at ? 'Review course' : 'Continue learning' }}</a>@endif
</article>
@empty
<div class="card empty-card"><h3>No course enrolments yet</h3><p class="muted">Explore the published courses and choose learning that supports your faith, leadership, career and life skills.</p><a class="btn btn-primary" href="{{ route('public.courses') }}">Explore courses</a></div>
@endforelse
</div>
<div class="pagination">{{ $enrolments->links() }}</div>
</section>

@if($availableCourses->isNotEmpty())
<section class="learning-section">
<div class="section-head"><div><span class="section-kicker">RECOMMENDED</span><h2>Available for you</h2></div></div>
<div class="course-grid">
@foreach($availableCourses as $course)
<article class="card course-card">
@if($course->image_path)<img class="course-image" src="{{ asset('storage/'.$course->image_path) }}" alt="">@else<div class="course-icon"><i class="{{ $course->icon_class ?: 'fas fa-graduation-cap' }}"></i></div>@endif
<h3>{{ $course->title }}</h3><p class="muted">{{ \Illuminate\Support\Str::limit($course->description,130) }}</p><a class="btn" href="{{ route('public.courses.show',$course) }}">View course</a>
</article>
@endforeach
</div>
</section>
@endif
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.learning-section{margin-bottom:34px}.section-head{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:15px}.section-head h2{margin:2px 0 0}.course-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.course-card{display:flex;flex-direction:column;gap:12px}.course-card h3{margin:0}.course-card .btn{margin-top:auto}.course-icon{width:46px;height:46px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center;font-size:1.15rem}.course-image{width:100%;height:150px;object-fit:cover}.course-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:.8rem;color:#475467}.progress-wrap{display:grid;grid-template-columns:1fr auto;align-items:center;gap:10px}.progress-wrap strong{font-size:.8rem;color:var(--primary)}.progress-bar{height:8px;background:#ece8f3;border-radius:999px;overflow:hidden}.progress-bar span{display:block;height:100%;background:var(--primary);border-radius:999px}.empty-card{grid-column:1/-1;text-align:center;padding:30px}@media(max-width:900px){.course-grid{grid-template-columns:1fr 1fr}}@media(max-width:620px){.course-grid{grid-template-columns:1fr}.section-head{align-items:flex-start;flex-direction:column}}
</style>
@endsection
