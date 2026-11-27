@extends('youth.layout')
@section('title', 'Home | COU Youth')
@section('youth_content')
@use('Illuminate\Support\Str')
@php
    $firstName = Str::of(auth()->user()->name)->trim()->explode(' ')->first() ?: auth()->user()->name;
    $localNow = \Carbon\CarbonImmutable::now(config('cou_youth.engagement_timezone', 'Africa/Kampala'));
    $current = $streak['current'];

    $streakLine = match (true) {
        $streak['checked_in_today'] && $current > 1 => 'Come back tomorrow to make it '.($current + 1).'.',
        $streak['checked_in_today'] => 'Come back tomorrow to start a streak.',
        $current > 0 => "Check in to keep your {$current}-day streak going.",
        $streak['total'] > 0 => 'Fresh start today. Every streak begins with day one.',
        default => 'Check in once a day and watch your streak grow.',
    };

    $nextMine = $upcomingEvents->first(fn ($event) => in_array($event->id, $registeredEventIds, true));
    $heroLine = match (true) {
        $nextMine !== null && $nextMine->starts_at?->isToday() => "{$nextMine->title} is today. Don't be late!",
        $nextMine !== null && $nextMine->starts_at?->lessThan(now()->addDays(7)) => "{$nextMine->title} is ".$nextMine->starts_at->diffForHumans().'.',
        $stats['unread'] > 0 => 'You have '.$stats['unread'].' new '.Str::plural('update', $stats['unread']).' waiting.',
        default => "Here's what's good today.",
    };

    $shareText = $verse ? '"'.$verse['text'].'" '.$verse['ref'] : '';
    $comingCount = $services->count() + $upcomingEvents->count();
@endphp

<header class="dash-head">
    <div>
        <p class="dash-date">{{ $localNow->format('l, j F') }}</p>
        <h1>{{ $greeting }}, <span class="scribble">{{ $firstName }}</span>.</h1>
        <p class="dash-line">{{ $heroLine }}</p>
    </div>
    <nav class="quick" aria-label="Shortcuts">
        <a href="{{ route('public.prayer') }}"><i class="fas fa-hands-praying" aria-hidden="true"></i> Prayer</a>
        <a href="{{ route('youth.media') }}"><i class="fas fa-headphones" aria-hidden="true"></i> Watch</a>
        <a href="{{ route('youth.life-groups') }}"><i class="fas fa-handshake" aria-hidden="true"></i> Groups</a>
        <a href="{{ route('youth.donations') }}"><i class="fas fa-heart" aria-hidden="true"></i> Give</a>
    </nav>
</header>

<div class="dash-grid">
    <div class="dash-col dash-main">

        {{-- Today: check in and keep the streak going --}}
        <section class="card panel today {{ $streak['checked_in_today'] ? 'is-done' : '' }}" aria-labelledby="streak-title" style="order:1">
            <div class="today-row">
                <div class="streak-count">
                    <span class="flame" aria-hidden="true"><i class="fas fa-fire"></i></span>
                    <div>
                        <strong id="streak-title">{{ $current }}</strong>
                        <span>day streak</span>
                    </div>
                </div>
                <ol class="week" aria-label="This week">
                    @foreach($streak['week'] as $day)
                        <li class="{{ $day['done'] ? 'done' : '' }} {{ $day['today'] ? 'today' : '' }} {{ $day['future'] ? 'future' : '' }}">
                            <span class="pip" aria-hidden="true">@if($day['done'])<i class="fas fa-check"></i>@endif</span>
                            <small>{{ Str::substr($day['label'], 0, 1) }}</small>
                            <span class="sr-only">{{ $day['label'] }}: {{ $day['done'] ? 'checked in' : ($day['future'] ? 'coming up' : 'missed') }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="today-action">
                    @if($streak['checked_in_today'])
                        <p class="checked"><i class="fas fa-circle-check"></i> Checked in today</p>
                    @else
                        <form method="POST" action="{{ route('youth.check-in') }}">
                            @csrf
                            <button class="btn btn-sun" type="submit"><i class="fas fa-hand-sparkles"></i> I'm here today</button>
                        </form>
                    @endif
                </div>
            </div>
            <p class="today-foot">
                <span>{{ $streakLine }}</span>
                @if($streak['next_milestone'])<span class="chip"><i class="fas fa-trophy"></i> {{ $streak['next_milestone'] - $current }} to your {{ $streak['next_milestone'] }}-day badge</span>@endif
                @if($streak['best'] > 1)<span class="chip">Best: {{ $streak['best'] }}</span>@endif
            </p>
        </section>

        {{-- Keep learning --}}
        <section class="card panel" data-collapsible="learn" style="order:3">
            <div class="panel-head">
                <h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-learn" data-collapse="learn"><i class="fas fa-book-open panel-icon" aria-hidden="true"></i><span>Keep learning</span><i class="fas fa-chevron-down chev" aria-hidden="true"></i></button></h2>
                <a class="panel-link" href="{{ route('youth.learning') }}">My courses</a>
            </div>
            <div class="collapse-body" id="dash-learn">
                @if($continueCourse)
                    @php($pct = (int) $continueCourse->progress_percent)
                    <a class="course-row" href="{{ route('public.courses.show', $continueCourse->course) }}">
                        <span class="row-icon"><i class="{{ $continueCourse->course->icon_class ?: 'fas fa-book-open' }}" aria-hidden="true"></i></span>
                        <span class="row-copy">
                            <strong>{{ $continueCourse->course->title }}</strong>
                            <span class="progress-line">
                                <span class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="Course progress"><span style="width:{{ min(100, max(3, $pct)) }}%"></span></span>
                                <b>{{ $pct }}%</b>
                            </span>
                            <small>{{ $pct === 0 ? 'Time to open lesson one.' : ($pct >= 75 ? 'Nearly there. Finish strong!' : 'Nice progress. Keep going.') }}</small>
                        </span>
                        <span class="btn btn-primary btn-sm">Continue</span>
                    </a>
                @elseif($suggestedCourse)
                    <a class="course-row" href="{{ route('public.courses.show', $suggestedCourse) }}">
                        <span class="row-icon alt"><i class="{{ $suggestedCourse->icon_class ?: 'fas fa-seedling' }}" aria-hidden="true"></i></span>
                        <span class="row-copy">
                            <small class="eyebrow">Something new to try</small>
                            <strong>{{ $suggestedCourse->title }}</strong>
                            @if($suggestedCourse->description)<small>{{ Str::limit($suggestedCourse->description, 90) }}</small>@endif
                        </span>
                        <span class="btn btn-sm">Take a look</span>
                    </a>
                @else
                    <p class="quiet"><i class="fas fa-seedling" aria-hidden="true"></i> No course on the go yet. <a href="{{ route('public.courses') }}">Browse courses</a></p>
                @endif
            </div>
        </section>

        {{-- Coming up --}}
        <section class="card panel" data-collapsible="coming" style="order:4">
            <div class="panel-head">
                <h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-coming" data-collapse="coming"><i class="fas fa-calendar-days panel-icon" aria-hidden="true"></i><span>Coming up</span>@if($comingCount)<span class="count">{{ $comingCount }}</span>@endif<i class="fas fa-chevron-down chev" aria-hidden="true"></i></button></h2>
                <a class="panel-link" href="{{ route('youth.calendar') }}">Calendar</a>
            </div>
            <div class="collapse-body list" id="dash-coming">
                @foreach($services as $service)
                    @php($starts = \Illuminate\Support\Carbon::parse($service->starts_at))
                    <a class="list-row {{ $service->status === 'live' ? 'is-live' : '' }}" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">
                        <span class="date-tile"><b>{{ $starts->format('j') }}</b><small>{{ $starts->format('M') }}</small></span>
                        <span class="row-copy">
                            <strong>{{ $service->title }}</strong>
                            <small>@if($service->status === 'live')<span class="live-pill"><span class="live-dot"></span> Live now</span>@else Online, {{ $starts->format('D g:i A') }}@endif @if($service->speaker), {{ $service->speaker }}@endif</small>
                        </span>
                        <i class="fas fa-play row-go" aria-hidden="true"></i>
                    </a>
                @endforeach
                @forelse($upcomingEvents as $event)
                    @php($going = in_array($event->id, $registeredEventIds, true))
                    <a class="list-row" href="{{ route('public.events.show', $event) }}">
                        <span class="date-tile"><b>{{ $event->starts_at?->format('j') }}</b><small>{{ $event->starts_at?->format('M') }}</small></span>
                        <span class="row-copy">
                            <strong>{{ $event->title }}</strong>
                            <small>{{ $event->starts_at?->format('D g:i A') }}@if($event->venue), {{ $event->venue }}@endif</small>
                        </span>
                        @if($going)<span class="badge badge-good"><i class="fas fa-check"></i> Going</span>@else<i class="fas fa-chevron-right row-go" aria-hidden="true"></i>@endif
                    </a>
                @empty
                    @if($services->isEmpty())
                        <p class="quiet"><i class="fas fa-calendar-days" aria-hidden="true"></i> Nothing on the calendar yet. <a href="{{ route('youth.life-groups') }}">Find a life group</a></p>
                    @endif
                @endforelse
            </div>
        </section>
    </div>

    <div class="dash-col dash-side">
        {{-- Verse of the day --}}
        @if($verse)
        <section class="card panel verse" aria-labelledby="verse-title" style="order:2">
            <p class="verse-tag" id="verse-title"><i class="fas fa-book-bible" aria-hidden="true"></i> Today's word</p>
            <blockquote>
                <p>{{ $verse['text'] }}</p>
                <cite>{{ $verse['ref'] }} @if($verse['translation'])<small>{{ $verse['translation'] }}</small>@endif</cite>
            </blockquote>
            <div class="verse-actions">
                <a class="btn btn-sm" href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener noreferrer" data-share-verse data-share-text="{{ $shareText }}"><i class="fab fa-whatsapp"></i> Share</a>
                <button class="btn btn-sm btn-ghost" type="button" data-copy-verse data-copy-text="{{ $shareText }}"><i class="fas fa-copy"></i> <span>Copy</span></button>
            </div>
        </section>
        @endif

        {{-- Updates --}}
        <section class="card panel" data-collapsible="team" style="order:5">
            <div class="panel-head">
                <h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-team" data-collapse="team"><i class="fas fa-bell panel-icon" aria-hidden="true"></i><span>From the team</span>@if($stats['unread'])<span class="count is-new">{{ $stats['unread'] }}</span>@endif<i class="fas fa-chevron-down chev" aria-hidden="true"></i></button></h2>
                <a class="panel-link" href="{{ route('youth.notifications') }}">See all</a>
            </div>
            <div class="collapse-body list" id="dash-team">
                @forelse($notifications as $receipt)
                    <form method="POST" action="{{ route('youth.notifications.read', $receipt) }}" class="update-row {{ $receipt->read_at ? '' : 'unread' }}">
                        @csrf @method('PATCH')
                        <button type="submit">
                            <span class="update-dot" aria-hidden="true"></span>
                            <span class="row-copy">
                                <strong>{{ $receipt->notification->title }}</strong>
                                <small>{{ Str::limit($receipt->notification->body, 70) }}</small>
                            </span>
                            <time datetime="{{ optional($receipt->created_at)->toIso8601String() }}">{{ optional($receipt->created_at)->diffForHumans(null, true, true) }}</time>
                        </button>
                    </form>
                @empty
                    <p class="quiet"><i class="fas fa-inbox" aria-hidden="true"></i> All quiet for now.</p>
                @endforelse
            </div>
        </section>

        {{-- Progress --}}
        <section class="card panel" data-collapsible="progress" style="order:6">
            <div class="panel-head">
                <h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-progress" data-collapse="progress"><i class="fas fa-chart-simple panel-icon" aria-hidden="true"></i><span>Your progress</span><i class="fas fa-chevron-down chev" aria-hidden="true"></i></button></h2>
            </div>
            <div class="collapse-body" id="dash-progress">
                <div class="stamps">
                    <a class="stamp s-sun" href="{{ route('youth.life-groups') }}"><b>{{ $stats['life_groups'] }}</b><span>{{ Str::plural('group', $stats['life_groups']) }}</span></a>
                    <a class="stamp s-coral" href="{{ route('youth.events') }}"><b>{{ $stats['events'] }}</b><span>{{ Str::plural('event', $stats['events']) }}</span></a>
                    <a class="stamp s-leaf" href="{{ route('youth.learning') }}"><b>{{ $stats['courses_done'] }}</b><span>{{ Str::plural('course', $stats['courses_done']) }} done</span></a>
                    <a class="stamp s-sky" href="{{ route('youth.certificates') }}"><b>{{ $stats['certificates'] }}</b><span>{{ Str::plural('certificate', $stats['certificates']) }}</span></a>
                </div>
                <a class="profile-nudge" href="{{ route('youth.profile') }}">
                    <span class="row-copy">
                        @if($profileCompletion < 100)
                            <strong>Your profile is {{ $profileCompletion }}% done</strong>
                            <span class="meter"><span style="width:{{ max(4, $profileCompletion) }}%"></span></span>
                        @else
                            <strong>Your profile is all set</strong>
                            <small>{{ $profile?->organisationUnit?->name ? 'Connected to '.$profile->organisationUnit->name : 'Thanks for keeping it up to date' }}</small>
                        @endif
                    </span>
                    <i class="fas fa-chevron-right row-go" aria-hidden="true"></i>
                </a>
            </div>
        </section>

        @if($currentTheme)
        <a class="card panel theme-link" href="{{ route('youth.annual-theme') }}" style="order:7">
            <span class="theme-year">{{ $currentTheme->year }} theme</span>
            <strong>{{ $currentTheme->theme }}</strong>
            @if(!empty($currentTheme->scripture_reference))<small><i class="fas fa-book-bible" aria-hidden="true"></i> {{ $currentTheme->scripture_reference }}</small>@endif
        </a>
        @endif
    </div>
</div>

<style>
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

/* Header */
.dash-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.dash-date{margin:0;font-size:.85rem;font-weight:700;color:var(--muted)}
.dash-head h1{font-size:clamp(1.6rem,3.4vw,2.2rem);line-height:1.1;letter-spacing:-.03em;margin:2px 0 4px;font-weight:800}
.dash-line{margin:0;color:var(--ink-soft)}
.quick{display:flex;gap:8px;flex-wrap:wrap}
.quick a{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:var(--r-btn);background:#fff;border:1px solid var(--line);font-weight:700;font-size:.88rem;color:var(--ink);transition:border-color .15s ease,transform .15s ease}
.quick a i{color:var(--primary)}
.quick a:hover{border-color:var(--edge-strong);transform:translateY(-1px)}

/* Layout: main column and a narrower side column */
.dash-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(0,1fr);gap:16px;align-items:start}
.dash-col{display:flex;flex-direction:column;gap:16px;min-width:0}

/* Panels share one look */
.panel{padding:14px 16px!important}
.panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px}
.panel-head h2{margin:0;font-size:1rem}
.collapse-btn{display:inline-flex;align-items:center;gap:8px;padding:0;border:0;background:none;font:inherit;font-weight:750;color:var(--ink);cursor:pointer;text-align:left}
.panel-icon{width:28px;height:28px;border-radius:9px;display:grid;place-items:center;background:var(--primary-soft);color:var(--primary);font-size:.8rem}
.chev{font-size:.7rem;color:var(--muted);transition:transform .2s ease}
.collapse-btn[aria-expanded=false] .chev{transform:rotate(-90deg)}
.collapse-btn:focus-visible{outline:3px solid var(--sun);outline-offset:4px;border-radius:8px}
.count{min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--paper-2);font-size:.72rem;font-weight:800;display:inline-grid;place-items:center}
.count.is-new{background:var(--coral);color:#fff}
.panel-link{font-size:.85rem;font-weight:700;color:var(--primary)}
.panel-link:hover{text-decoration:underline!important}
.collapse-body{margin-top:12px}
.collapse-body[hidden]{display:none}
.quiet{margin:0;padding:6px 2px;color:var(--muted);font-size:.92rem}
.quiet i{color:var(--primary);margin-right:4px}
.quiet a{color:var(--primary);font-weight:700}
.row-copy{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.row-copy strong{font-size:.95rem;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.row-copy small{color:var(--muted);font-size:.82rem}
.row-go{color:var(--muted);font-size:.8rem}
body .btn.btn-sm{min-height:34px!important;padding:5px 14px;font-size:.84rem}

/* Today */
.today-row{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.streak-count{display:flex;align-items:center;gap:10px}
.flame{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:var(--coral-soft);color:var(--coral);font-size:1.15rem;border:var(--bw) solid var(--edge)}
.today.is-done .flame{background:var(--coral);color:#fff;animation:flicker 2.4s ease-in-out infinite}
@keyframes flicker{0%,100%{transform:rotate(calc(-3deg * var(--tilt))) scale(1)}50%{transform:rotate(calc(3deg * var(--tilt))) scale(1.05)}}
.streak-count strong{display:block;font-family:var(--font-display);font-size:1.7rem;line-height:.95;font-weight:800;color:var(--ink)}
.streak-count span{font-size:.8rem;font-weight:700;color:var(--ink-soft)}
.week{list-style:none;margin:0;padding:0;display:flex;gap:5px;flex:1;min-width:220px}
.week li{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px}
.week .pip{width:100%;max-width:30px;aspect-ratio:1;border-radius:9px;border:1.5px dashed color-mix(in srgb,var(--ink) 25%,var(--line));display:grid;place-items:center;font-size:.68rem}
.week .done .pip{background:var(--sun);border:var(--bw) solid var(--edge);color:var(--ink)}
.week .today .pip{outline:2px solid color-mix(in srgb,var(--primary) 35%,transparent);outline-offset:2px}
.week .future .pip{opacity:.45}
.week small{font-size:.66rem;font-weight:800;color:var(--muted)}
.week .today small{color:var(--ink)}
.today-action form{margin:0}
.checked{margin:0;display:flex;align-items:center;gap:7px;padding:7px 12px;border-radius:var(--r-btn);background:var(--leaf-soft);color:var(--leaf);font-weight:800;font-size:.88rem}
.today-foot{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:12px 0 0;padding-top:10px;border-top:1px dashed var(--line);font-size:.88rem;color:var(--ink-soft)}
.today-foot>span:first-child{margin-right:auto}
.today-foot .chip{font-size:.76rem;padding:3px 9px}

/* Keep learning */
.course-row{display:flex;align-items:center;gap:12px;padding:10px;margin:-2px -4px;border-radius:12px}
.course-row:hover{background:var(--surface-soft)}
.row-icon{width:40px;height:40px;flex:0 0 40px;border-radius:12px;display:grid;place-items:center;background:var(--primary-soft);color:var(--primary)}
.row-icon.alt{background:var(--leaf-soft);color:var(--leaf)}
.progress-line{display:flex;align-items:center;gap:8px}
.progress-line .meter{flex:1;height:7px}
.progress-line b{font-size:.8rem}
.eyebrow{font-weight:800;color:var(--primary)!important;font-size:.74rem!important}

/* Lists inside panels */
.list{display:flex;flex-direction:column}
.list-row{display:flex;align-items:center;gap:12px;padding:8px 6px;border-radius:12px}
.list-row+.list-row{border-top:1px solid var(--line);border-radius:0}
.list-row:hover{background:var(--surface-soft)}
.list-row .date-tile{width:42px;height:44px;flex-basis:42px;border-radius:11px}
.list-row .date-tile b{font-size:1rem}
.list-row.is-live .date-tile{background:var(--coral);color:#fff}
.live-pill{display:inline-flex;align-items:center;gap:5px;font-weight:800;color:var(--coral)}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--coral);animation:pulse 1.4s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* Verse */
.verse{background:var(--primary)!important;border-color:transparent!important;color:#fff}
.verse-tag{margin:0;display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:800;color:var(--sun)}
.verse blockquote{margin:8px 0 12px}
.verse blockquote p{margin:0;font-family:var(--font-display);font-size:1.05rem;line-height:1.4;font-weight:600;color:#fff}
.verse cite{display:block;margin-top:8px;font-style:normal;font-weight:800;font-size:.88rem;color:var(--sun)}
.verse cite small{font-weight:600;color:rgba(255,255,255,.65);margin-left:4px}
.verse-actions{display:flex;gap:8px}
body .verse .btn{background:#fff;border-color:#fff;color:var(--ink)}
body .verse .btn:hover,body .verse .btn:focus-visible{background:var(--sun);border-color:var(--sun);color:var(--ink)}
body .verse .btn.btn-ghost{background:transparent;border-color:rgba(255,255,255,.45);color:#fff}
body .verse .btn.btn-ghost:hover,body .verse .btn.btn-ghost:focus-visible{background:rgba(255,255,255,.12);border-color:#fff;color:#fff}

/* Progress */
.stamps{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.stamp{display:flex;align-items:baseline;gap:6px;padding:10px 12px;border-radius:12px;transition:transform .15s ease}
.stamp:hover{transform:translateY(-1px)}
.stamp b{font-family:var(--font-display);font-size:1.35rem;line-height:1;color:var(--ink)}
.stamp span{font-weight:700;color:var(--ink-soft);font-size:.8rem}
.s-sun{background:var(--sun-soft)}.s-coral{background:var(--coral-soft)}.s-leaf{background:var(--leaf-soft)}.s-sky{background:var(--sky-soft)}
.profile-nudge{display:flex;align-items:center;gap:10px;margin-top:10px;padding:10px 12px;border-radius:12px;border:1px dashed var(--line)}
.profile-nudge:hover{border-style:solid;border-color:var(--edge-strong)}
.profile-nudge .meter{height:6px;margin-top:4px}

/* Updates */
.update-row{margin:0}
.update-row+.update-row{border-top:1px solid var(--line)}
.update-row button{width:100%;display:flex;align-items:flex-start;gap:10px;padding:9px 4px;border:0;background:none;text-align:left;cursor:pointer;color:inherit;border-radius:10px}
.update-row button:hover{background:var(--surface-soft)}
.update-dot{width:8px;height:8px;flex:0 0 8px;margin-top:6px;border-radius:50%}
.update-row.unread .update-dot{background:var(--coral)}
.update-row:not(.unread) .row-copy strong{font-weight:600;color:var(--ink-soft)}
.update-row time{font-size:.74rem;color:var(--muted);white-space:nowrap}

/* Theme */
.theme-link{display:flex;flex-direction:column;gap:3px;background:var(--ink)!important;border-color:var(--ink)!important;color:#fff}
.theme-year{font-size:.78rem;font-weight:800;color:var(--sun)}
.theme-link strong{font-family:var(--font-display);font-size:1.02rem;line-height:1.3}
.theme-link small{color:rgba(255,255,255,.7);font-size:.8rem}

/* Folded panels shrink to their header */
[data-collapsible].is-collapsed .panel-link{display:none}

@media(max-width:1000px){
    /* One column, ordered by what matters most each day. */
    .dash-grid{display:flex;flex-direction:column}
    .dash-col{display:contents}
}
@media(max-width:620px){
    .quick{width:100%;flex-wrap:nowrap;overflow-x:auto;padding-bottom:2px;scrollbar-width:none}
    .quick a{flex:0 0 auto}
    .today-row{gap:12px}
    .today-action,.today-action .btn{width:100%}
    .week{min-width:0;width:100%}
    .update-row time{display:none}
    .course-row .btn{display:none}
}
@media(prefers-reduced-motion:reduce){.today.is-done .flame,.live-dot{animation:none}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Collapsible panels; remembers what each person folded away.
    let folded = [];
    try { folded = JSON.parse(localStorage.getItem('cou.dashboard.collapsed') || '[]'); } catch (_) {}
    document.querySelectorAll('[data-collapse]').forEach(btn => {
        const key = btn.dataset.collapse;
        const body = document.getElementById(btn.getAttribute('aria-controls'));
        const panel = btn.closest('[data-collapsible]');
        const set = open => {
            btn.setAttribute('aria-expanded', String(open));
            body.hidden = !open;
            panel.classList.toggle('is-collapsed', !open);
        };
        set(!folded.includes(key));
        btn.addEventListener('click', () => {
            const open = btn.getAttribute('aria-expanded') !== 'true';
            set(open);
            folded = open ? folded.filter(k => k !== key) : [...new Set([...folded, key])];
            try { localStorage.setItem('cou.dashboard.collapsed', JSON.stringify(folded)); } catch (_) {}
        });
    });

    const share = document.querySelector('[data-share-verse]');
    if (share && navigator.share) {
        share.addEventListener('click', event => {
            event.preventDefault();
            navigator.share({ text: share.dataset.shareText }).catch(() => {});
        });
    }
    const copy = document.querySelector('[data-copy-verse]');
    if (copy && navigator.clipboard) {
        copy.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(copy.dataset.copyText);
                copy.querySelector('span').textContent = 'Copied!';
                setTimeout(() => { copy.querySelector('span').textContent = 'Copy'; }, 1800);
            } catch (_) {}
        });
    } else if (copy) {
        copy.hidden = true;
    }
});
</script>
@endsection
