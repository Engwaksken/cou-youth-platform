@extends('youth.layout')
@section('title', 'Home | COU Youth')
@section('youth_content')
@use('Illuminate\Support\Str')
@php
    $firstName = Str::of(auth()->user()->name)->trim()->explode(' ')->first() ?: auth()->user()->name;
    $localNow = \Carbon\CarbonImmutable::now(config('cou_youth.engagement_timezone', 'Africa/Kampala'));
    $current = $streak['current'];

    $streakLine = match (true) {
        $streak['checked_in_today'] && $current > 1 => "You're in for today. Come back tomorrow to make it ".($current + 1).'.',
        $streak['checked_in_today'] => "You're in for today. Come back tomorrow and start a streak.",
        $current > 0 => "Your {$current}-day streak is still going. Check in to keep it alive.",
        $streak['total'] > 0 => 'Fresh start today. Every streak begins with day one.',
        default => 'Check in once a day and watch your streak grow.',
    };

    $nextMine = $upcomingEvents->first(fn ($event) => in_array($event->id, $registeredEventIds, true));
    $heroLine = match (true) {
        $nextMine !== null && $nextMine->starts_at?->isToday() => "{$nextMine->title} is today. Don't be late!",
        $nextMine !== null && $nextMine->starts_at?->lessThan(now()->addDays(7)) => "{$nextMine->title} is ".$nextMine->starts_at->diffForHumans().'.',
        $stats['unread'] > 0 => 'You have '.$stats['unread'].' new '.Str::plural('update', $stats['unread']).' waiting for you.',
        default => "Here's what's good today.",
    };

    $shareText = $verse ? '"'.$verse['text'].'" '.$verse['ref'] : '';
@endphp

<header class="today-hero">
    <p class="today-date">{{ $localNow->format('l, j F') }}</p>
    <h1>{{ $greeting }}, <span class="scribble">{{ $firstName }}</span>.</h1>
    <p>{{ $heroLine }}</p>
</header>

<div class="today-grid">
    {{-- Daily check-in and streak --}}
    <section class="card streak-card {{ $streak['checked_in_today'] ? 'is-done' : '' }}" aria-labelledby="streak-title">
        <div class="streak-top">
            <div class="streak-count">
                <span class="flame" aria-hidden="true"><i class="fas fa-fire"></i></span>
                <div>
                    <strong id="streak-title">{{ $current }}</strong>
                    <span>day streak</span>
                </div>
            </div>
            @if($streak['best'] > 1)<span class="chip"><i class="fas fa-trophy"></i> Best: {{ $streak['best'] }}</span>@endif
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

        <p class="streak-line">{{ $streakLine }}</p>

        @if($streak['checked_in_today'])
            <p class="checked"><i class="fas fa-circle-check"></i> Checked in today</p>
        @else
            <form method="POST" action="{{ route('youth.check-in') }}">
                @csrf
                <button class="btn btn-sun btn-block" type="submit"><i class="fas fa-hand-sparkles"></i> I'm here today</button>
            </form>
        @endif

        @if($streak['next_milestone'])
            <p class="milestone">{{ $streak['next_milestone'] - $current }} more {{ Str::plural('day', $streak['next_milestone'] - $current) }} to your {{ $streak['next_milestone'] }}-day badge</p>
        @endif
    </section>

    {{-- Verse of the day --}}
    @if($verse)
    <section class="card verse-card" aria-labelledby="verse-title">
        <span class="section-kicker" id="verse-title">Today's word</span>
        <blockquote>
            <p>{{ $verse['text'] }}</p>
            <cite>{{ $verse['ref'] }} @if($verse['translation'])<small>{{ $verse['translation'] }}</small>@endif</cite>
        </blockquote>
        <div class="verse-actions">
            <a class="btn" href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener noreferrer" data-share-verse data-share-text="{{ $shareText }}"><i class="fab fa-whatsapp"></i> Share with a friend</a>
            <button class="btn btn-ghost" type="button" data-copy-verse data-copy-text="{{ $shareText }}"><i class="fas fa-copy"></i> <span>Copy</span></button>
        </div>
    </section>
    @endif
</div>

{{-- Pick up where you left off --}}
<section class="block" data-collapsible="continue">
    <div class="section-head"><h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-continue" data-collapse="continue"><span>Pick up where you left off</span><i class="fas fa-chevron-down" aria-hidden="true"></i></button></h2><a class="btn btn-ghost" href="{{ route('youth.learning') }}">All my courses <i class="fas fa-arrow-right"></i></a></div>
    <div class="collapse-body" id="dash-continue">
    @if($continueCourse)
        <a class="card continue-card" href="{{ route('public.courses.show', $continueCourse->course) }}">
            <span class="continue-art" aria-hidden="true"><i class="{{ $continueCourse->course->icon_class ?: 'fas fa-book-open' }}"></i></span>
            <div class="continue-body">
                <h3>{{ $continueCourse->course->title }}</h3>
                <div class="continue-progress">
                    <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $continueCourse->progress_percent }}" aria-label="Course progress"><span style="width:{{ min(100, max(3, (int) $continueCourse->progress_percent)) }}%"></span></div>
                    <strong>{{ (int) $continueCourse->progress_percent }}%</strong>
                </div>
                <p class="muted">{{ (int) $continueCourse->progress_percent === 0 ? 'You signed up. Time to open lesson one.' : ((int) $continueCourse->progress_percent >= 75 ? 'Nearly there. Finish strong!' : 'Nice progress. Keep the momentum going.') }}</p>
            </div>
            <span class="btn btn-primary">Continue <i class="fas fa-arrow-right"></i></span>
        </a>
    @elseif($suggestedCourse)
        <a class="card continue-card" href="{{ route('public.courses.show', $suggestedCourse) }}">
            <span class="continue-art alt" aria-hidden="true"><i class="{{ $suggestedCourse->icon_class ?: 'fas fa-seedling' }}"></i></span>
            <div class="continue-body">
                <span class="badge badge-sun">Something new to try</span>
                <h3>{{ $suggestedCourse->title }}</h3>
                @if($suggestedCourse->description)<p class="muted">{{ Str::limit($suggestedCourse->description, 120) }}</p>@endif
            </div>
            <span class="btn btn-primary">Take a look <i class="fas fa-arrow-right"></i></span>
        </a>
    @else
        <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-seedling"></i></span><h3>No courses on the go</h3><p class="muted">When you start a course it'll show up here so you can jump straight back in.</p><a class="btn btn-primary" href="{{ route('public.courses') }}">Browse courses</a></div>
    @endif
    </div>
</section>

{{-- Coming up --}}
<section class="block" data-collapsible="coming-up">
    <div class="section-head"><h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-coming-up" data-collapse="coming-up"><span>Coming up</span><i class="fas fa-chevron-down" aria-hidden="true"></i></button></h2><a class="btn btn-ghost" href="{{ route('youth.calendar') }}">Full calendar <i class="fas fa-arrow-right"></i></a></div>
    <div class="collapse-body" id="dash-coming-up">
    <div class="upcoming">
        @foreach($services as $service)
            @php($starts = \Illuminate\Support\Carbon::parse($service->starts_at))
            <a class="card up-row {{ $service->status === 'live' ? 'is-live' : '' }}" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">
                <span class="date-tile"><b>{{ $starts->format('j') }}</b><small>{{ $starts->format('M') }}</small></span>
                <span class="up-copy">
                    @if($service->status === 'live')<span class="live-pill"><span class="live-dot"></span> Live now</span>@else<span class="badge">Online</span>@endif
                    <strong>{{ $service->title }}</strong>
                    <small>{{ $starts->format('D, g:i A') }}@if($service->speaker), {{ $service->speaker }}@endif</small>
                </span>
                <i class="fas fa-play up-go" aria-hidden="true"></i>
            </a>
        @endforeach
        @forelse($upcomingEvents as $event)
            @php($going = in_array($event->id, $registeredEventIds, true))
            <a class="card up-row" href="{{ route('public.events.show', $event) }}">
                <span class="date-tile"><b>{{ $event->starts_at?->format('j') }}</b><small>{{ $event->starts_at?->format('M') }}</small></span>
                <span class="up-copy">
                    @if($going)<span class="badge badge-good"><i class="fas fa-check"></i> You're going</span>@endif
                    <strong>{{ $event->title }}</strong>
                    <small>{{ $event->starts_at?->format('D, g:i A') }}@if($event->venue), {{ $event->venue }}@endif</small>
                </span>
                <i class="fas fa-arrow-right up-go" aria-hidden="true"></i>
            </a>
        @empty
            @if($services->isEmpty())
                <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-calendar-days"></i></span><h3>Nothing on the calendar yet</h3><p class="muted">New events land here first. Check back soon, or find your local fellowship in the meantime.</p><a class="btn" href="{{ route('youth.life-groups') }}">Find a life group</a></div>
            @endif
        @endforelse
    </div>
    </div>
</section>

<div class="split">
    {{-- Latest updates --}}
    <section class="block" data-collapsible="team">
        <div class="section-head"><h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-team" data-collapse="team"><span>From the team</span><i class="fas fa-chevron-down" aria-hidden="true"></i></button></h2><a class="btn btn-ghost" href="{{ route('youth.notifications') }}">See all <i class="fas fa-arrow-right"></i></a></div>
        <div class="collapse-body" id="dash-team">
        <div class="card updates-card">
            @forelse($notifications as $receipt)
                <form method="POST" action="{{ route('youth.notifications.read', $receipt) }}" class="update-row {{ $receipt->read_at ? '' : 'unread' }}">
                    @csrf @method('PATCH')
                    <button type="submit">
                        <span class="update-dot" aria-hidden="true"></span>
                        <span class="update-copy">
                            <strong>{{ $receipt->notification->title }}</strong>
                            <small>{{ Str::limit($receipt->notification->body, 90) }}</small>
                        </span>
                        <time datetime="{{ optional($receipt->created_at)->toIso8601String() }}">{{ optional($receipt->created_at)->diffForHumans(null, true, true) }}</time>
                    </button>
                </form>
            @empty
                <p class="quiet"><i class="fas fa-inbox" aria-hidden="true"></i> All quiet for now. News from your church and the youth team will show up here.</p>
            @endforelse
        </div>
        </div>
    </section>

    {{-- Journey + profile --}}
    <section class="block" data-collapsible="journey">
        <div class="section-head"><h2><button class="collapse-btn" type="button" aria-expanded="true" aria-controls="dash-journey" data-collapse="journey"><span>Your journey so far</span><i class="fas fa-chevron-down" aria-hidden="true"></i></button></h2></div>
        <div class="collapse-body" id="dash-journey">
        <div class="card journey-card">
            <div class="stamps">
                <a class="stamp s-sun" href="{{ route('youth.life-groups') }}"><b>{{ $stats['life_groups'] }}</b><span>{{ Str::plural('life group', $stats['life_groups']) }}</span></a>
                <a class="stamp s-coral" href="{{ route('youth.events') }}"><b>{{ $stats['events'] }}</b><span>{{ Str::plural('event', $stats['events']) }} joined</span></a>
                <a class="stamp s-leaf" href="{{ route('youth.learning') }}"><b>{{ $stats['courses_done'] }}</b><span>{{ Str::plural('course', $stats['courses_done']) }} finished</span></a>
                <a class="stamp s-sky" href="{{ route('youth.certificates') }}"><b>{{ $stats['certificates'] }}</b><span>{{ Str::plural('certificate', $stats['certificates']) }}</span></a>
            </div>
            <div class="profile-nudge">
                @if($profileCompletion < 100)
                    <div>
                        <strong>Your profile is {{ $profileCompletion }}% done</strong>
                        <small>Tell us what you're into and we'll point you to groups and courses that fit.</small>
                        <div class="meter"><span style="width:{{ max(4, $profileCompletion) }}%"></span></div>
                    </div>
                    <a class="btn btn-primary" href="{{ route('youth.profile') }}">Finish it</a>
                @else
                    <div>
                        <strong>Your profile is all set</strong>
                        <small>{{ $profile?->organisationUnit?->name ? 'Connected to '.$profile->organisationUnit->name.'.' : 'Thanks for keeping it up to date.' }}</small>
                    </div>
                    <a class="btn" href="{{ route('youth.profile') }}">Edit</a>
                @endif
            </div>
        </div>
        </div>
    </section>
</div>

@if($currentTheme)
<a class="theme-banner" href="{{ route('youth.annual-theme') }}">
    <span class="theme-year">{{ $currentTheme->year }} theme</span>
    <strong>{{ $currentTheme->theme }}</strong>
    @if(!empty($currentTheme->scripture_reference))<small><i class="fas fa-book-bible"></i> {{ $currentTheme->scripture_reference }}</small>@endif
    <i class="fas fa-arrow-right theme-go" aria-hidden="true"></i>
</a>
@endif

<nav class="quick" aria-label="Shortcuts">
    <a href="{{ route('public.prayer') }}"><i class="fas fa-hands-praying" aria-hidden="true"></i> Ask for prayer</a>
    <a href="{{ route('youth.media') }}"><i class="fas fa-headphones" aria-hidden="true"></i> Watch & listen</a>
    <a href="{{ route('youth.life-groups') }}"><i class="fas fa-handshake" aria-hidden="true"></i> Find your people</a>
    <a href="{{ route('youth.donations') }}"><i class="fas fa-heart" aria-hidden="true"></i> Give</a>
</nav>

<style>
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.today-hero{margin:0 0 22px}
.today-date{margin:0;font-weight:700;color:var(--muted)}
.today-hero h1{font-size:clamp(2.2rem,5.5vw,3.6rem);line-height:1.02;letter-spacing:-.04em;margin:4px 0 10px;font-weight:800}
.today-hero p:last-child{margin:0;font-size:1.1rem;color:var(--ink-soft)}
.today-grid{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:18px;margin-bottom:34px}

.streak-card{padding:22px;display:flex;flex-direction:column;gap:14px}
.streak-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
.streak-count{display:flex;align-items:center;gap:12px}
.flame{width:58px;height:58px;border-radius:18px;display:grid;place-items:center;background:var(--coral-soft);color:var(--coral);font-size:1.7rem;border:var(--bw) solid var(--edge)}
.streak-card.is-done .flame{background:var(--coral);color:#fff;animation:flicker 2.4s ease-in-out infinite}
@keyframes flicker{0%,100%{transform:rotate(calc(-3deg * var(--tilt))) scale(1)}50%{transform:rotate(calc(3deg * var(--tilt))) scale(1.05)}}
.streak-count strong{display:block;font-family:var(--font-display);font-size:2.6rem;line-height:.9;font-weight:800;color:var(--ink)}
.streak-count span{font-weight:700;color:var(--ink-soft)}
.week{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
.week li{display:flex;flex-direction:column;align-items:center;gap:5px}
.week .pip{width:100%;max-width:38px;aspect-ratio:1;border-radius:12px;border:1.5px dashed color-mix(in srgb,var(--ink) 25%,var(--line));display:grid;place-items:center;font-size:.8rem}
.week .done .pip{background:var(--sun);border:var(--bw) solid var(--edge);color:var(--ink)}
.week .today .pip{outline:3px solid color-mix(in srgb,var(--primary) 30%,transparent);outline-offset:2px}
.week .future .pip{opacity:.45}
.week small{font-size:.72rem;font-weight:800;color:var(--muted)}
.week .today small{color:var(--ink)}
.streak-line{margin:0;color:var(--ink-soft)}
.checked{margin:0;display:flex;align-items:center;gap:8px;padding:11px 14px;border-radius:14px;background:var(--leaf-soft);color:var(--leaf);font-weight:800}
.milestone{margin:0;font-size:.85rem;color:var(--muted)}
.btn-block{width:100%}

.verse-card{padding:24px;background:var(--primary)!important;border-color:var(--edge-strong)!important;color:#fff;display:flex;flex-direction:column;position:relative;overflow:hidden}
.verse-card .section-kicker{color:var(--ink)!important;background:var(--sun);border-radius:6px;padding:2px 8px;align-self:flex-start}
.verse-card blockquote{margin:14px 0 18px;flex:1}
.verse-card blockquote p{margin:0;font-family:var(--font-display);font-size:clamp(1.25rem,2.4vw,1.6rem);line-height:1.3;font-weight:600;letter-spacing:-.015em;color:#fff}
.verse-card cite{display:block;margin-top:12px;font-style:normal;font-weight:800;color:var(--sun)}
.verse-card cite small{font-weight:600;color:rgba(255,255,255,.65);margin-left:4px}
.verse-actions{display:flex;gap:8px;flex-wrap:wrap}
body .verse-card .btn{background:#fff;border-color:#fff;color:var(--ink)}
body .verse-card .btn:hover,body .verse-card .btn:focus-visible{background:var(--sun);border-color:var(--sun);color:var(--ink)}
body .verse-card .btn.btn-ghost{background:transparent;border-color:rgba(255,255,255,.45);color:#fff}
body .verse-card .btn.btn-ghost:hover,body .verse-card .btn.btn-ghost:focus-visible{background:rgba(255,255,255,.12);border-color:#fff;color:#fff}

.block{margin-bottom:34px}
.collapse-btn{display:inline-flex;align-items:center;gap:10px;padding:0;border:0;background:none;font:inherit;color:inherit;letter-spacing:inherit;cursor:pointer;text-align:left}
.collapse-btn i{font-size:.75em;color:var(--muted);transition:transform .2s ease}
.collapse-btn:hover i{color:var(--ink)}
.collapse-btn[aria-expanded=false] i{transform:rotate(-90deg)}
.collapse-btn:focus-visible{outline:3px solid var(--sun);outline-offset:4px;border-radius:8px}
[data-collapsible].is-collapsed .section-head{margin-bottom:0}
[data-collapsible].is-collapsed .section-head>.btn{display:none}
.collapse-body[hidden]{display:none}
.split .block.is-collapsed .card{flex:0}

.continue-card{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:18px;padding:18px}
.continue-art{width:72px;height:72px;border-radius:20px;display:grid;place-items:center;font-size:1.8rem;background:var(--primary-soft);color:var(--primary);border:var(--bw) solid var(--edge);transform:rotate(calc(-4deg * var(--tilt)))}
.continue-art.alt{background:var(--leaf-soft);color:var(--leaf)}
.continue-body h3{margin:2px 0 8px;font-size:1.25rem}
.continue-body p{margin:6px 0 0}
.continue-progress{display:flex;align-items:center;gap:10px}
.continue-progress .meter{flex:1}
.continue-progress strong{font-family:var(--font-display)}

.upcoming{display:grid;gap:10px}
.up-row{display:flex;align-items:center;gap:16px;padding:12px 16px 12px 12px}
.up-copy{flex:1;min-width:0;display:flex;flex-direction:column;align-items:flex-start;gap:3px}
.up-copy strong{font-size:1.05rem;color:var(--ink)}
.up-copy small{color:var(--muted)}
.up-go{color:var(--muted)}
.up-row:hover .up-go{color:var(--ink)}
.up-row.is-live{border-color:var(--coral)!important;background:linear-gradient(90deg,var(--coral-soft),#fff 60%)}
.up-row.is-live .date-tile{background:var(--coral);color:#fff}
.live-pill{display:inline-flex;align-items:center;gap:6px;font-size:.75rem;font-weight:800;color:var(--coral);text-transform:uppercase;letter-spacing:.05em}
.live-dot{width:8px;height:8px;border-radius:50%;background:var(--coral);animation:pulse 1.4s ease-in-out infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(255,107,74,.5)}50%{box-shadow:0 0 0 6px rgba(255,107,74,0)}}

.split{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.split .block{display:flex;flex-direction:column}
.split .card{flex:1}
.updates-card{padding:6px}
.update-row{margin:0}
.update-row button{width:100%;display:flex;align-items:flex-start;gap:12px;padding:12px;border:0;background:none;border-radius:14px;text-align:left;cursor:pointer;color:inherit}
.update-row button:hover{background:var(--surface-soft)}
.update-row+.update-row{border-top:1px solid var(--line)}
.update-dot{width:9px;height:9px;flex:0 0 9px;margin-top:7px;border-radius:50%;background:transparent}
.update-row.unread .update-dot{background:var(--coral)}
.update-copy{flex:1;min-width:0}
.update-copy strong,.update-copy small{display:block}
.update-row:not(.unread) .update-copy strong{font-weight:600;color:var(--ink-soft)}
.update-copy small{color:var(--muted);margin-top:2px}
.update-row time{font-size:.78rem;color:var(--muted);white-space:nowrap}
.quiet{margin:0;padding:22px 16px;color:var(--muted)}

.journey-card{padding:18px;display:flex;flex-direction:column;gap:16px}
.stamps{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.stamp{display:flex;align-items:baseline;gap:8px;padding:14px;border-radius:16px;transition:transform .15s ease}
.stamp:hover{transform:rotate(calc(-1.5deg * var(--tilt))) scale(1.02)}
.stamp b{font-family:var(--font-display);font-size:1.9rem;line-height:1;color:var(--ink)}
.stamp span{font-weight:700;color:var(--ink-soft);font-size:.9rem;line-height:1.2}
.s-sun{background:var(--sun-soft)}.s-coral{background:var(--coral-soft)}.s-leaf{background:var(--leaf-soft)}.s-sky{background:var(--sky-soft)}
.profile-nudge{display:flex;align-items:center;gap:14px;padding:14px;border-radius:16px;border:1.5px dashed var(--line)}
.profile-nudge>div{flex:1;min-width:0}
.profile-nudge strong,.profile-nudge small{display:block}
.profile-nudge small{color:var(--muted);margin:2px 0 8px}

.theme-banner{position:relative;display:flex;flex-direction:column;gap:4px;padding:22px 64px 22px 24px;margin-bottom:28px;border-radius:var(--r-card);background:var(--ink);color:#fff;overflow:hidden;transition:transform .18s ease,box-shadow .18s ease}
.theme-banner::after{content:'';position:absolute;right:-40px;bottom:-60px;width:180px;height:180px;border-radius:50%;background:var(--sun);opacity:.9}
.theme-banner:hover{transform:translate(-2px,-2px);box-shadow:4px 4px 0 var(--sun)}
.theme-year{font-weight:800;color:var(--sun);font-size:.9rem}
.theme-banner strong{font-family:var(--font-display);font-size:clamp(1.3rem,3vw,1.8rem);line-height:1.15;max-width:44rem;position:relative;z-index:1}
.theme-banner small{color:rgba(255,255,255,.75);position:relative;z-index:1}
.theme-go{position:absolute;right:26px;top:50%;transform:translateY(-50%);color:var(--ink);z-index:1;font-size:1.2rem}

.quick{display:flex;gap:10px;flex-wrap:wrap}
.quick a{display:inline-flex;align-items:center;gap:8px;padding:11px 16px;border-radius:999px;background:#fff;border:1.5px solid var(--line);font-weight:700;color:var(--ink);transition:.15s ease}
.quick a i{color:var(--primary)}
.quiet i{color:var(--primary);margin-right:4px}
.quick a:hover{border-color:var(--edge-strong);transform:translateY(-2px)}

@media(max-width:1000px){.today-grid,.split{grid-template-columns:1fr}}
@media(max-width:620px){
    .continue-card{grid-template-columns:auto minmax(0,1fr)}
    .continue-card>.btn{grid-column:1/-1;width:100%}
    .continue-art{width:56px;height:56px;font-size:1.4rem}
    .profile-nudge{flex-direction:column;align-items:stretch}
    .quick a{flex:1 1 calc(50% - 10px);justify-content:center}
    .update-row time{display:none}
}
@media(prefers-reduced-motion:reduce){.streak-card.is-done .flame,.live-dot{animation:none}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Collapsible sections; remembers what each person folded away.
    let folded = [];
    try { folded = JSON.parse(localStorage.getItem('cou.dashboard.collapsed') || '[]'); } catch (_) {}
    document.querySelectorAll('[data-collapse]').forEach(btn => {
        const key = btn.dataset.collapse;
        const body = document.getElementById(btn.getAttribute('aria-controls'));
        const section = btn.closest('[data-collapsible]');
        const set = open => {
            btn.setAttribute('aria-expanded', String(open));
            body.hidden = !open;
            section.classList.toggle('is-collapsed', !open);
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
