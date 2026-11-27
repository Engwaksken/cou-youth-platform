@extends('youth.layout')
@section('title', 'My Learning | COU Youth')
@section('youth_content')
@php
    $pageEnrolments = collect($enrolments->items());
    $finished = $pageEnrolments->filter(fn ($item) => ! empty($item->completed_at))->count();
@endphp
<section class="hero">
    <h1>My <span class="scribble">learning</span></h1>
    <p>
        @if($enrolments->total() === 0)
            Bible studies and life-skills courses you can take at your own pace, on your phone, whenever you've got a few minutes.
        @else
            {{ $enrolments->total() }} {{ \Illuminate\Support\Str::plural('course', $enrolments->total()) }} on your shelf{{ $finished ? ', '.$finished.' finished' : '' }}. A little bit every day adds up.
        @endif
    </p>
</section>

<section class="learning-section">
    <div class="section-head"><h2>Your courses</h2><a class="btn" href="{{ route('public.courses') }}"><i class="fas fa-magnifying-glass"></i> Find more</a></div>
    <div class="course-grid">
        @forelse($enrolments as $enrolment)
            @php($pct = (int) $enrolment->progress_percent)
            <article class="card course-card {{ $enrolment->completed_at ? 'is-finished' : '' }}">
                <div class="course-top">
                    <span class="course-icon" aria-hidden="true"><i class="{{ $enrolment->course?->icon_class ?: 'fas fa-book-open' }}"></i></span>
                    @if($enrolment->completed_at)
                        <span class="badge badge-good"><i class="fas fa-check"></i> Finished {{ $enrolment->completed_at->format('j M') }}</span>
                    @elseif($pct > 0)
                        <span class="badge badge-sun">In progress</span>
                    @else
                        <span class="badge">Not started</span>
                    @endif
                </div>
                <h3>{{ $enrolment->course?->title ?? 'Course no longer available' }}</h3>
                @if($enrolment->course?->description)<p class="muted">{{ \Illuminate\Support\Str::limit($enrolment->course->description, 120) }}</p>@endif
                <div class="progress-wrap">
                    <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="Course progress"><span style="width:{{ min(100, max(0, $pct)) }}%"></span></div>
                    <strong>{{ $pct }}%</strong>
                </div>
                @if($enrolment->course)
                    <a class="btn {{ $enrolment->completed_at ? '' : 'btn-primary' }}" href="{{ route('public.courses.show', $enrolment->course) }}">
                        {{ $enrolment->completed_at ? 'Look back through it' : ($pct > 0 ? 'Keep going' : 'Start lesson one') }} <i class="fas fa-arrow-right"></i>
                    </a>
                @endif
            </article>
        @empty
            <div class="card empty-card">
                <span class="empty-emoji" aria-hidden="true"><i class="fas fa-book-open"></i></span>
                <h3>Your shelf is empty, for now</h3>
                <p class="muted">Pick a course about faith, leadership, careers or life. Short lessons, no pressure.</p>
                <a class="btn btn-primary" href="{{ route('public.courses') }}">Browse courses</a>
            </div>
        @endforelse
    </div>
    <div class="pagination">{{ $enrolments->links() }}</div>
</section>

@if($availableCourses->isNotEmpty())
<section class="learning-section">
    <div class="section-head"><div><h2>You might like</h2><p class="muted">Picked for your age group.</p></div></div>
    <div class="course-grid">
        @foreach($availableCourses as $course)
            <a class="card course-card" href="{{ route('public.courses.show', $course) }}">
                @if($course->image_path)
                    <img class="course-image" src="{{ asset('storage/'.$course->image_path) }}" alt="">
                @else
                    <span class="course-icon alt" aria-hidden="true"><i class="{{ $course->icon_class ?: 'fas fa-seedling' }}"></i></span>
                @endif
                <h3>{{ $course->title }}</h3>
                <p class="muted">{{ \Illuminate\Support\Str::limit($course->description, 110) }}</p>
                <span class="more-link">Have a look <i class="fas fa-arrow-right"></i></span>
            </a>
        @endforeach
    </div>
</section>
@endif

<style>
.learning-section{margin-bottom:38px}
.course-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.course-card{display:flex;flex-direction:column;gap:12px;padding:20px}
.course-card h3{margin:0}
.course-card p{margin:0}
.course-card .btn{margin-top:auto;align-self:flex-start}
.course-card.is-finished{background:linear-gradient(180deg,var(--leaf-soft),#fff 45%)}
.course-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.course-icon{width:52px;height:52px;border-radius:16px;display:grid;place-items:center;font-size:1.3rem;background:var(--primary-soft);color:var(--primary);border:var(--bw) solid var(--edge);transform:rotate(calc(-4deg * var(--tilt)))}
.course-icon.alt{background:var(--sun-soft);color:#8a5a00}
.course-image{width:100%;height:150px;object-fit:cover;border-radius:14px}
.progress-wrap{display:flex;align-items:center;gap:10px;margin-top:auto}
.progress-wrap .meter{flex:1}
.progress-wrap strong{font-family:var(--font-display);font-size:.95rem}
.more-link{margin-top:auto;font-weight:800;color:var(--primary)}
.empty-card{grid-column:1/-1}
@media(max-width:1000px){.course-grid{grid-template-columns:1fr 1fr}}
@media(max-width:620px){.course-grid{grid-template-columns:1fr}.course-card .btn{align-self:stretch}}
</style>
@endsection
