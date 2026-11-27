@extends(auth()->check() ? 'youth.layout' : 'public.layout')
@section('title', ($term !== '' ? $term.', ' : '').'Search | COU Youth')
@section(auth()->check() ? 'youth_content' : 'content')
@php
    $meta = [
        'events' => ['Events', 'fa-ticket', 'public.events'],
        'courses' => ['Courses', 'fa-book-open', 'public.courses'],
        'life_groups' => ['Life groups', 'fa-people-group', 'public.life-groups'],
        'stories' => ['Stories & resources', 'fa-newspaper', 'public.news'],
        'churches' => ['Churches', 'fa-church', 'public.churches'],
    ];
@endphp

<section class="hero search-hero">
    <h1>@if($term !== '')Results for <span class="scribble">"{{ $term }}"</span>@else What are you <span class="scribble">looking for?</span>@endif</h1>
    <form class="big-search" method="GET" action="{{ route('public.search') }}" role="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <label class="sr-only" for="bigSearch">Search events, courses, groups, stories and churches</label>
        <input id="bigSearch" type="search" name="q" value="{{ $term }}" placeholder="Try worship, Mukono or leadership" maxlength="100" @if($term === '') autofocus @endif>
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
    @if($term !== '')
        <p>{{ $total ? $total.' '.\Illuminate\Support\Str::plural('match', $total).' across the site.' : '' }}</p>
    @endif
</section>

@if($term !== '' && mb_strlen($term) < 2)
    <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span><h3>Type a little more</h3><p class="muted">Use at least two letters so we can find a good match.</p></div>
@elseif($term !== '' && $groups === [])
    <div class="card empty-card">
        <span class="empty-emoji" aria-hidden="true"><i class="fas fa-circle-question"></i></span>
        <h3>Nothing matched "{{ $term }}"</h3>
        <p class="muted">Check the spelling, try a shorter word, or have a browse instead.</p>
        <div class="browse-links">
            @foreach($meta as [$label, $icon, $route])<a class="chip" href="{{ route($route) }}"><i class="fas {{ $icon }}"></i> {{ $label }}</a>@endforeach
        </div>
    </div>
@elseif($term === '')
    <div class="browse-links">
        @foreach($meta as [$label, $icon, $route])<a class="chip" href="{{ route($route) }}"><i class="fas {{ $icon }}"></i> {{ $label }}</a>@endforeach
    </div>
@endif

@foreach($groups as $key => $group)
    @php([$label, $icon, $route] = $meta[$key])
    <section class="result-group" aria-labelledby="results-{{ $key }}">
        <div class="section-head">
            <h2 id="results-{{ $key }}"><i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span class="count">{{ $group['total'] }}</span></h2>
            @if($group['total'] > $group['items']->count())
                <a class="btn btn-ghost" href="{{ route($route, ['q' => $term]) }}">See all {{ $group['total'] }} <i class="fas fa-arrow-right"></i></a>
            @endif
        </div>
        <div class="card result-list">
            @foreach($group['items'] as $item)
                @switch($key)
                    @case('events')
                        <a class="result" href="{{ route('public.events.show', $item) }}">
                            <span class="date-tile"><b>{{ $item->starts_at?->format('j') }}</b><small>{{ $item->starts_at?->format('M') }}</small></span>
                            <span class="result-copy"><strong>{{ $item->title }}</strong><small>{{ $item->starts_at?->format('D j M Y, g:i A') }}@if($item->venue), {{ $item->venue }}@endif</small></span>
                            @if($item->starts_at?->isPast())<span class="badge">Past</span>@endif
                        </a>
                        @break
                    @case('courses')
                        <a class="result" href="{{ route('public.courses.show', $item) }}">
                            <span class="result-icon"><i class="{{ $item->icon_class ?: 'fas fa-book-open' }}"></i></span>
                            <span class="result-copy"><strong>{{ $item->title }}</strong><small>{{ \Illuminate\Support\Str::limit($item->description, 110) }}</small></span>
                        </a>
                        @break
                    @case('life_groups')
                        <a class="result" href="{{ route('public.life-groups', ['q' => $item->name]) }}">
                            <span class="result-icon"><i class="fas fa-people-group"></i></span>
                            <span class="result-copy"><strong>{{ $item->name }}</strong><small>{{ $item->organisationUnit?->name ?? 'Church of Uganda Youth' }}@if($item->meeting_day), {{ $item->meeting_day }}@endif @if($item->meeting_location), {{ $item->meeting_location }}@endif</small></span>
                        </a>
                        @break
                    @case('stories')
                        <a class="result" href="{{ route('public.news', ['q' => $item->title]) }}">
                            <span class="result-icon"><i class="fas fa-newspaper"></i></span>
                            <span class="result-copy"><strong>{{ $item->title }}</strong><small>{{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body ?? ''), 110) }}</small></span>
                        </a>
                        @break
                    @case('churches')
                        <a class="result" href="{{ route('public.churches', ['q' => $item->name]) }}">
                            <span class="result-icon"><i class="fas fa-church"></i></span>
                            <span class="result-copy"><strong>{{ $item->name }}</strong><small>{{ $item->address ?: ($item->organisationUnit?->name ?? '') }}</small></span>
                        </a>
                        @break
                @endswitch
            @endforeach
        </div>
    </section>
@endforeach

<style>
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.search-hero h1{overflow-wrap:anywhere}
.big-search{position:relative;display:flex;gap:8px;max-width:44rem;margin-top:18px}
.big-search>i{position:absolute;left:18px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
body .big-search input{flex:1;height:54px!important;min-height:54px!important;padding:0 16px 0 46px!important;font-size:1.05rem!important;border:var(--bw) solid var(--edge);border-radius:999px!important;background:#fff}
.big-search input:focus{outline:none;box-shadow:0 0 0 4px var(--sun-soft)}
body .big-search .btn{min-height:54px!important;padding-inline:24px}
.search-hero>p{margin-top:12px}
.browse-links{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:14px}
.hero+.browse-links{justify-content:flex-start;margin-top:0}
.browse-links .chip{background:#fff;border:1.5px solid var(--line);padding:8px 14px}
.browse-links .chip:hover{border-color:var(--edge-strong);color:var(--ink)}
.result-group{margin-bottom:30px;max-width:56rem}
.result-group h2{display:flex;align-items:center;gap:10px;font-size:1.3rem}
.result-group h2 i{color:var(--primary);font-size:1.05rem}
.result-group .count{display:inline-grid;place-items:center;min-width:26px;height:26px;padding:0 8px;border-radius:999px;background:var(--sun);font-family:var(--font-body);font-size:.8rem;font-weight:800}
.result-list{padding:6px!important}
.result{display:flex;align-items:center;gap:14px;padding:12px;border-radius:14px}
.result:hover{background:var(--surface-soft)}
.result+.result{border-top:1px solid var(--line)}
.result .date-tile{width:50px;height:54px;flex-basis:50px}
.result .date-tile b{font-size:1.2rem}
.result-icon{width:50px;height:50px;flex:0 0 50px;border-radius:14px;display:grid;place-items:center;background:var(--primary-soft);color:var(--primary);font-size:1.1rem}
.result-copy{flex:1;min-width:0}
.result-copy strong,.result-copy small{display:block}
.result-copy strong{color:var(--ink)}
.result-copy small{color:var(--muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
@media(max-width:560px){.big-search{flex-direction:column}.big-search>i{top:27px}body .big-search .btn{width:100%}}
</style>
@endsection
