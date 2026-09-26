@extends('youth.layout')
@section('title', 'My Dashboard | COU Youth')
@section('youth_content')
<section class="page-head">
    <div>
        <h1><i class="fas fa-gauge-high"></i> Dashboard</h1>
        <p>Live overview of your youth engagement, learning, events, prayer support and resources.</p>
    </div>
</section>

<div class="stats-grid">
    <a class="stat-card c1" href="{{ route('youth.life-groups') }}"><span class="stat-icon"><i class="fas fa-people-group"></i></span><div><strong>{{ $stats['life_groups'] }}</strong><span>Life Groups</span></div></a>
    <a class="stat-card c2" href="{{ route('youth.learning') }}"><span class="stat-icon"><i class="fas fa-graduation-cap"></i></span><div><strong>{{ $stats['courses'] }}</strong><span>Courses</span></div></a>
    <a class="stat-card c3" href="{{ route('youth.events') }}"><span class="stat-icon"><i class="fas fa-calendar-check"></i></span><div><strong>{{ $stats['events'] }}</strong><span>My Events</span></div></a>
    <a class="stat-card c4" href="{{ route('youth.certificates') }}"><span class="stat-icon"><i class="fas fa-award"></i></span><div><strong>{{ $stats['certificates'] }}</strong><span>Certificates</span></div></a>
    <a class="stat-card c5" href="{{ route('youth.notifications') }}"><span class="stat-icon"><i class="fas fa-bell"></i></span><div><strong>{{ $stats['unread'] }}</strong><span>Unread Alerts</span></div></a>
    <a class="stat-card c6" href="{{ route('public.prayer') }}"><span class="stat-icon"><i class="fas fa-hands-praying"></i></span><div><strong>{{ $stats['prayers'] }}</strong><span>Prayer Requests</span></div></a>
    <a class="stat-card c7" href="{{ route('youth.media') }}"><span class="stat-icon"><i class="fas fa-photo-film"></i></span><div><strong>{{ $stats['media'] }}</strong><span>Media Resources</span></div></a>
    <a class="stat-card c8" href="{{ route('youth.media') }}"><span class="stat-icon"><i class="fas fa-video"></i></span><div><strong>{{ $stats['services'] }}</strong><span>Online Services</span></div></a>
</div>

<div class="tabs" role="tablist">
    <button class="tab active" type="button" data-tab="overview"><i class="fas fa-table-cells-large"></i> Overview</button>
    <button class="tab" type="button" data-tab="learning"><i class="fas fa-book-open"></i> Learning</button>
    <button class="tab" type="button" data-tab="updates"><i class="fas fa-bell"></i> Updates</button>
    <button class="tab" type="button" data-tab="ministry"><i class="fas fa-church"></i> Ministry</button>
</div>

<section class="tab-panel active" id="tab-overview">
<div class="dashboard-grid">
    <article class="card dashboard-panel">
        <div class="panel-title"><div><span class="section-kicker">PROFILE</span><h2>Your profile</h2></div><a class="btn" href="{{ route('youth.profile') }}">Edit</a></div>
        @if($profile)
        <dl class="profile-summary">
            <div><dt>Age category</dt><dd>{{ str_replace('_', ' ', ucfirst($profile->age_category)) }}</dd></div>
            <div><dt>Church / unit</dt><dd>{{ $profile->organisationUnit?->name ?? 'Not selected' }}</dd></div>
            <div><dt>School / institution</dt><dd>{{ $profile->school_institution ?: 'Not added' }}</dd></div>
            <div><dt>Visibility</dt><dd>{{ $profile->profile_public ? 'Public' : 'Private' }}</dd></div>
        </dl>
        @else
        <p class="muted">Complete your profile to receive more relevant groups, learning and opportunities.</p><a class="btn btn-primary" href="{{ route('youth.profile') }}">Complete profile</a>
        @endif
    </article>

    <article class="card dashboard-panel">
        <div class="panel-title"><div><span class="section-kicker">ANNUAL THEME</span><h2>{{ $currentTheme?->year ?? now()->year }}</h2></div><a href="{{ route('youth.annual-theme') }}">View all</a></div>
        @if($currentTheme)
            <h3 class="theme-title">{{ $currentTheme->theme }}</h3>
            @if(!empty($currentTheme->scripture_reference))<p class="theme-scripture"><i class="fas fa-book-bible"></i> {{ $currentTheme->scripture_reference }}</p>@endif
            @if(!empty($currentTheme->description))<p class="muted">{{ \Illuminate\Support\Str::limit($currentTheme->description,180) }}</p>@endif
        @else
            <p class="muted">No published annual theme is available yet.</p>
        @endif
        <div class="shortcut-row"><a href="{{ route('youth.calendar') }}"><i class="fas fa-calendar-days"></i> Calendar</a><a href="{{ route('public.donate') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-hand-holding-heart"></i> Donate</a></div>
    </article>
</div>
</section>

<section class="tab-panel" id="tab-learning">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">LEARNING</span><h2>Recent courses</h2></div><a href="{{ route('youth.learning') }}">View all</a></div>
    <div class="stack-list">
    @forelse($enrolments as $enrolment)
        <a class="list-row" href="{{ $enrolment->course ? route('public.courses.show', $enrolment->course) : route('youth.learning') }}"><span class="list-icon"><i class="fas fa-book-open"></i></span><span><strong>{{ $enrolment->course?->title ?? 'Course' }}</strong><small>{{ $enrolment->completed_at ? 'Completed' : ((int) $enrolment->progress_percent > 0 ? (int) $enrolment->progress_percent.'% complete' : 'Enrolled') }}</small></span><i class="fas fa-chevron-right"></i></a>
    @empty
        <p class="muted">You have no course enrolments yet.</p><a class="btn" href="{{ route('public.courses') }}">Browse courses</a>
    @endforelse
    </div>
</div>
</section>

<section class="tab-panel" id="tab-updates">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">UPDATES</span><h2>Notifications</h2></div><a href="{{ route('youth.notifications') }}">View all</a></div>
    <div class="stack-list">
    @forelse($notifications as $receipt)
        @if($receipt->notification)
        <a class="list-row {{ $receipt->read_at ? '' : 'unread-row' }}" href="#" onclick="event.preventDefault();this.nextElementSibling.submit();"><span class="list-icon"><i class="fas fa-bell"></i></span><span><strong>{{ $receipt->notification->title }}</strong><small>{{ \Illuminate\Support\Str::limit($receipt->notification->body,80) }}</small></span>@if(!$receipt->read_at)<span class="unread-dot"></span>@endif</a>
        <form method="POST" action="{{ route('youth.notifications.read',$receipt) }}" class="hidden-form">@csrf @method('PATCH')</form>
        @endif
    @empty
        <p class="muted">No notifications yet.</p>
    @endforelse
    </div>
</div>
</section>

<section class="tab-panel" id="tab-ministry">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">ONLINE</span><h2>Upcoming services</h2></div><a href="{{ route('youth.media') }}">Media & services</a></div>
    <div class="stack-list">
    @forelse($upcomingServices as $service)
        <a class="list-row" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer"><span class="list-icon"><i class="fas fa-video"></i></span><span><strong>{{ $service->title }}</strong><small>{{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('D, d M Y · H:i') }} · {{ ucfirst($service->status) }}</small></span><i class="fas fa-arrow-up-right-from-square"></i></a>
    @empty
        <p class="muted">There are no upcoming online services at the moment.</p>
    @endforelse
    </div>
</div>
</section>

<style>
.page-head{margin-bottom:18px}.page-head h1{margin:0;font-size:28px}.page-head h1 i{color:var(--primary);margin-right:8px}.page-head p{margin:7px 0 0;color:var(--muted)}
.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.stat-card{display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:16px;padding:16px;box-shadow:var(--shadow-sm)}.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}.stat-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:var(--primary-light);color:var(--primary);font-size:18px}.stat-card strong{display:block;font-size:1.35rem}.stat-card span:last-child{display:block;color:var(--muted);font-size:.8rem;margin-top:2px}.c2{border-left-color:#15803d}.c3{border-left-color:#2563eb}.c4{border-left-color:#9333ea}.c5{border-left-color:#d97706}.c6{border-left-color:#dc2626}.c7{border-left-color:#0891b2}.c8{border-left-color:#0f766e}
.tabs{display:flex;gap:5px;border-bottom:1px solid var(--border);margin:0 0 18px;overflow-x:auto}.tab{border:0;background:transparent;padding:10px 13px;font-weight:800;color:var(--muted);border-bottom:3px solid transparent;border-radius:9px 9px 0 0;cursor:pointer;white-space:nowrap}.tab:hover,.tab.active{background:var(--primary-soft);color:var(--primary)}.tab.active{border-color:var(--primary)}.tab-panel{display:none}.tab-panel.active{display:block}.dashboard-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.dashboard-panel{padding:18px}.panel-title{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-title h2{font-size:1.1rem;margin:2px 0 0}.panel-title>a:not(.btn){font-weight:800;color:var(--primary);font-size:.86rem}.profile-summary{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:0}.profile-summary div{background:var(--surface-soft);padding:12px;border:1px solid var(--border);border-radius:10px}.profile-summary dt{font-size:.75rem;color:var(--muted);font-weight:800}.profile-summary dd{margin:3px 0 0;font-weight:800}.theme-title{margin:2px 0 8px}.theme-scripture{font-weight:800;color:var(--primary)}.shortcut-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:15px}.shortcut-row a{padding:9px 11px;background:var(--primary-soft);color:var(--primary);border-radius:9px;font-weight:800}.stack-list{display:flex;flex-direction:column;gap:8px}.list-row{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:11px;padding:10px;border:1px solid var(--border);border-radius:10px;background:#fff}.list-row:hover{border-color:#c9bee0;background:#fbfaff}.list-row strong,.list-row small{display:block}.list-row small{font-size:.78rem;color:var(--muted);margin-top:2px}.list-icon{width:36px;height:36px;border-radius:9px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}.unread-row{border-left:3px solid var(--primary)}.unread-dot{width:9px;height:9px;background:var(--primary);border-radius:50%}.hidden-form{display:none}@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:800px){.dashboard-grid{grid-template-columns:1fr}}@media(max-width:520px){.stats-grid,.profile-summary{grid-template-columns:1fr}}
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.tabs .tab').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.tabs .tab').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.getElementById('tab-'+btn.dataset.tab)?.classList.add('active')}))});</script>
@endsection
