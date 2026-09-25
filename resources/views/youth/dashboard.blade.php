@extends('public.layout')
@section('title', 'My Dashboard | COU Youth')
@section('content')
<section class="hero youth-hero">
    <span class="section-kicker">MY COU YOUTH</span>
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p>Keep track of your learning, Life Groups, prayer support, notifications and upcoming online services in one place.</p>
</section>

<div class="youth-stats">
    <a class="stat-card" href="{{ route('youth.life-groups') }}"><i class="fas fa-people-group"></i><div><strong>{{ $stats['life_groups'] }}</strong><span>Life Groups</span></div></a>
    <a class="stat-card" href="{{ route('youth.learning') }}"><i class="fas fa-graduation-cap"></i><div><strong>{{ $stats['courses'] }}</strong><span>Courses</span></div></a>
    <a class="stat-card" href="{{ route('youth.notifications') }}"><i class="fas fa-bell"></i><div><strong>{{ $stats['unread'] }}</strong><span>Unread alerts</span></div></a>
    <a class="stat-card" href="{{ route('public.prayer') }}"><i class="fas fa-hands-praying"></i><div><strong>{{ $stats['prayers'] }}</strong><span>Prayer requests</span></div></a>
</div>

<div class="dashboard-grid">
<section class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">PROFILE</span><h2>Your profile</h2></div><a class="btn" href="{{ route('youth.profile') }}">Edit profile</a></div>
    @if($profile)
        <dl class="profile-summary">
            <div><dt>Age category</dt><dd>{{ str_replace('_', ' ', ucfirst($profile->age_category)) }}</dd></div>
            <div><dt>Church / unit</dt><dd>{{ $profile->organisationUnit?->name ?? 'Not selected' }}</dd></div>
            <div><dt>School / institution</dt><dd>{{ $profile->school_institution ?: 'Not added' }}</dd></div>
            <div><dt>Profile visibility</dt><dd>{{ $profile->profile_public ? 'Public' : 'Private' }}</dd></div>
        </dl>
    @else
        <p class="muted">Complete your youth profile so the platform can show more relevant groups, learning and opportunities.</p>
        <a class="btn btn-primary" href="{{ route('youth.profile') }}">Complete profile</a>
    @endif
</section>

<section class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">LEARNING</span><h2>Recent courses</h2></div><a href="{{ route('youth.learning') }}">View all</a></div>
    <div class="stack-list">
    @forelse($enrolments as $enrolment)
        <a class="list-row" href="{{ $enrolment->course ? route('public.courses.show', $enrolment->course) : route('youth.learning') }}">
            <span class="list-icon"><i class="fas fa-book-open"></i></span>
            <span><strong>{{ $enrolment->course?->title ?? 'Course' }}</strong><small>{{ $enrolment->completed_at ? 'Completed' : ((int) $enrolment->progress_percent > 0 ? (int) $enrolment->progress_percent.'% complete' : 'Enrolled') }}</small></span>
            <i class="fas fa-chevron-right"></i>
        </a>
    @empty
        <p class="muted">You have no course enrolments yet. Explore the learning catalogue to get started.</p>
        <a class="btn" href="{{ route('public.courses') }}">Browse courses</a>
    @endforelse
    </div>
</section>

<section class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">UPDATES</span><h2>Notifications</h2></div><a href="{{ route('youth.notifications') }}">View all</a></div>
    <div class="stack-list">
    @forelse($notifications as $receipt)
        @if($receipt->notification)
        <a class="list-row {{ $receipt->read_at ? '' : 'unread-row' }}" href="{{ route('youth.notifications.read', $receipt) }}" onclick="event.preventDefault(); this.nextElementSibling.submit();">
            <span class="list-icon"><i class="fas fa-bell"></i></span>
            <span><strong>{{ $receipt->notification->title }}</strong><small>{{ \Illuminate\Support\Str::limit($receipt->notification->body, 70) }}</small></span>
            @if(!$receipt->read_at)<span class="unread-dot" aria-label="Unread"></span>@endif
        </a>
        <form method="POST" action="{{ route('youth.notifications.read', $receipt) }}" class="hidden-form">@csrf @method('PATCH')</form>
        @endif
    @empty
        <p class="muted">No notifications yet.</p>
    @endforelse
    </div>
</section>

<section class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">ONLINE</span><h2>Upcoming services</h2></div><a href="{{ route('youth.media') }}">Media & services</a></div>
    <div class="stack-list">
    @forelse($upcomingServices as $service)
        <a class="list-row" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">
            <span class="list-icon"><i class="fas fa-video"></i></span>
            <span><strong>{{ $service->title }}</strong><small>{{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('D, d M Y · H:i') }} · {{ ucfirst($service->status) }}</small></span>
            <i class="fas fa-arrow-up-right-from-square"></i>
        </a>
    @empty
        <p class="muted">There are no upcoming online services at the moment.</p>
    @endforelse
    </div>
</section>
</div>

<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}
.youth-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}.stat-card{background:#fff;border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:10px;padding:16px;display:flex;align-items:center;gap:13px;box-shadow:var(--shadow-sm)}.stat-card>i{width:42px;height:42px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center}.stat-card strong{display:block;font-size:1.35rem;line-height:1.1}.stat-card span{font-size:.84rem;color:var(--muted);font-weight:700}.dashboard-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.dashboard-panel{padding:18px}.panel-title{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-title h2{font-size:1.15rem;margin:2px 0 0}.panel-title>a:not(.btn){font-weight:800;color:var(--primary);font-size:.86rem}.profile-summary{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:0}.profile-summary div{background:var(--surface-soft);padding:12px;border:1px solid var(--border);border-radius:10px}.profile-summary dt{font-size:.75rem;color:var(--muted);font-weight:800}.profile-summary dd{margin:3px 0 0;font-weight:800}.stack-list{display:flex;flex-direction:column;gap:8px}.list-row{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:11px;padding:10px;border:1px solid var(--border);border-radius:10px;background:#fff}.list-row:hover{border-color:#c9bee0;background:#fbfaff}.list-row strong,.list-row small{display:block}.list-row small{font-size:.78rem;color:var(--muted);margin-top:2px}.list-icon{width:36px;height:36px;border-radius:9px;background:#f3eef9;color:var(--primary);display:grid;place-items:center}.unread-row{border-left:3px solid var(--primary)}.unread-dot{width:9px;height:9px;background:var(--primary);border-radius:50%}.hidden-form{display:none}@media(max-width:900px){.youth-stats{grid-template-columns:1fr 1fr}.dashboard-grid{grid-template-columns:1fr}}@media(max-width:520px){.youth-stats{grid-template-columns:1fr}.profile-summary{grid-template-columns:1fr}}
</style>
@endsection
