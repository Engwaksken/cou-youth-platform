@extends('youth.layout')
@section('title', 'My Dashboard | COU Youth')
@section('youth_content')
@php
    $activeTab = request('tab');
    if (!in_array($activeTab, ['overview','learning','updates','ministry'], true)) {
        $activeTab = request()->has('learning_page') ? 'learning' : (request()->has('updates_page') ? 'updates' : (request()->has('ministry_page') ? 'ministry' : 'overview'));
    }
    $periodLabels = ['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','all'=>'All Time'];
@endphp

<section class="page-head">
    <div>
        <h1><i class="fas fa-gauge-high"></i> Dashboard</h1>
        <p>Live overview of your youth engagement, learning, events, prayer support and resources.</p>
    </div>
</section>

<form class="dashboard-filters" method="GET" action="{{ route('youth.dashboard') }}">
    <input type="hidden" name="tab" id="dashboardActiveTab" value="{{ $activeTab }}">
    <div class="search-field">
        <i class="fas fa-magnifying-glass"></i>
        <input type="search" name="q" value="{{ $search }}" placeholder="Search courses, notifications or services..." aria-label="Search dashboard">
    </div>
    <select name="period" aria-label="Filter dashboard by period">
        @foreach($periodLabels as $value=>$label)
            <option value="{{ $value }}" @selected($period===$value)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
    @if($search !== '' || $period !== 'month')
        <a class="btn btn-light" href="{{ route('youth.dashboard') }}"><i class="fas fa-rotate-left"></i> Reset</a>
    @endif
</form>

<div class="filter-summary">
    <span><i class="fas fa-calendar-range"></i> {{ $periodLabels[$period] ?? 'This Month' }}</span>
    @if($search !== '')<span><i class="fas fa-magnifying-glass"></i> “{{ $search }}”</span>@endif
</div>

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

<div class="tabs" id="dashboard-tabs" role="tablist">
    <button class="tab {{ $activeTab==='overview' ? 'active' : '' }}" type="button" data-tab="overview"><i class="fas fa-table-cells-large"></i> Overview</button>
    <button class="tab {{ $activeTab==='learning' ? 'active' : '' }}" type="button" data-tab="learning"><i class="fas fa-book-open"></i> Learning <span class="tab-count">{{ $enrolments->total() }}</span></button>
    <button class="tab {{ $activeTab==='updates' ? 'active' : '' }}" type="button" data-tab="updates"><i class="fas fa-bell"></i> Updates <span class="tab-count">{{ $notifications->total() }}</span></button>
    <button class="tab {{ $activeTab==='ministry' ? 'active' : '' }}" type="button" data-tab="ministry"><i class="fas fa-church"></i> Ministry <span class="tab-count">{{ $upcomingServices->total() }}</span></button>
</div>

<section class="tab-panel {{ $activeTab==='overview' ? 'active' : '' }}" id="tab-overview">
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
        <div class="shortcut-row">
            <a href="{{ route('youth.calendar') }}"><i class="fas fa-calendar-days"></i> Calendar</a>
            <a href="{{ route('public.donate') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-hand-holding-heart"></i> Donate</a>
            <a href="{{ route('youth.media') }}"><i class="fas fa-photo-film"></i> Media</a>
            <a href="{{ route('public.prayer') }}"><i class="fas fa-hands-praying"></i> Prayer</a>
        </div>
    </article>
</div>
</section>

<section class="tab-panel {{ $activeTab==='learning' ? 'active' : '' }}" id="tab-learning">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">LEARNING</span><h2>Course activity</h2></div><a href="{{ route('youth.learning') }}">View all</a></div>
    <div class="stack-list">
    @forelse($enrolments as $enrolment)
        <a class="list-row" href="{{ $enrolment->course ? route('public.courses.show', $enrolment->course) : route('youth.learning') }}"><span class="list-icon"><i class="fas fa-book-open"></i></span><span><strong>{{ $enrolment->course?->title ?? 'Course' }}</strong><small>{{ $enrolment->completed_at ? 'Completed' : ((int) $enrolment->progress_percent > 0 ? (int) $enrolment->progress_percent.'% complete' : 'Enrolled') }} · {{ optional($enrolment->created_at)->format('d M Y') }}</small></span><i class="fas fa-chevron-right"></i></a>
    @empty
        <div class="empty-state"><i class="fas fa-book-open"></i><h3>No matching course activity</h3><p>Try another period or search term.</p></div>
    @endforelse
    </div>
    @if($enrolments->hasPages())<div class="dashboard-pagination">{{ $enrolments->appends(['tab'=>'learning'])->fragment('dashboard-tabs')->links() }}</div>@endif
</div>
</section>

<section class="tab-panel {{ $activeTab==='updates' ? 'active' : '' }}" id="tab-updates">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">UPDATES</span><h2>Notifications</h2></div><a href="{{ route('youth.notifications') }}">View all</a></div>
    <div class="stack-list">
    @forelse($notifications as $receipt)
        @if($receipt->notification)
        <a class="list-row {{ $receipt->read_at ? '' : 'unread-row' }}" href="#" onclick="event.preventDefault();this.nextElementSibling.submit();"><span class="list-icon"><i class="fas fa-bell"></i></span><span><strong>{{ $receipt->notification->title }}</strong><small>{{ \Illuminate\Support\Str::limit($receipt->notification->body,80) }} · {{ optional($receipt->created_at)->format('d M Y') }}</small></span>@if(!$receipt->read_at)<span class="unread-dot"></span>@endif</a>
        <form method="POST" action="{{ route('youth.notifications.read',$receipt) }}" class="hidden-form">@csrf @method('PATCH')</form>
        @endif
    @empty
        <div class="empty-state"><i class="fas fa-bell-slash"></i><h3>No matching notifications</h3><p>Try another period or search term.</p></div>
    @endforelse
    </div>
    @if($notifications->hasPages())<div class="dashboard-pagination">{{ $notifications->appends(['tab'=>'updates'])->fragment('dashboard-tabs')->links() }}</div>@endif
</div>
</section>

<section class="tab-panel {{ $activeTab==='ministry' ? 'active' : '' }}" id="tab-ministry">
<div class="card dashboard-panel">
    <div class="panel-title"><div><span class="section-kicker">ONLINE</span><h2>Services</h2></div><a href="{{ route('youth.media') }}">Media & services</a></div>
    <div class="stack-list">
    @forelse($upcomingServices as $service)
        <a class="list-row" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer"><span class="list-icon"><i class="fas fa-video"></i></span><span><strong>{{ $service->title }}</strong><small>{{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('D, d M Y · H:i') }} · {{ ucfirst($service->status) }}@if($service->platform) · {{ $service->platform }}@endif</small></span><i class="fas fa-arrow-up-right-from-square"></i></a>
    @empty
        <div class="empty-state"><i class="fas fa-video-slash"></i><h3>No matching services</h3><p>Try another period or search term.</p></div>
    @endforelse
    </div>
    @if($upcomingServices->hasPages())<div class="dashboard-pagination">{{ $upcomingServices->appends(['tab'=>'ministry'])->fragment('dashboard-tabs')->links() }}</div>@endif
</div>
</section>

<style>
.page-head{margin-bottom:16px}.page-head h1{margin:0;font-size:28px}.page-head h1 i{color:var(--primary);margin-right:8px}.page-head p{margin:7px 0 0;color:var(--muted)}
.dashboard-filters{display:grid;grid-template-columns:minmax(260px,1fr) 170px auto auto;gap:10px;align-items:center;margin-bottom:9px}.search-field{position:relative}.search-field i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted)}.search-field input,.dashboard-filters select{width:100%;height:42px;border:1px solid #d0d5dd;border-radius:10px;background:#fff;color:var(--text)}.search-field input{padding:9px 12px 9px 38px}.dashboard-filters select{padding:9px 11px}.search-field input:focus,.dashboard-filters select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}.filter-summary{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px}.filter-summary span{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;background:var(--primary-soft);color:var(--primary);font-size:.76rem;font-weight:800}
.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.stat-card{display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:16px;padding:16px;box-shadow:var(--shadow-sm);transition:.18s ease}.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}.stat-icon{width:46px;height:46px;min-width:46px;border-radius:12px;display:grid;place-items:center;background:var(--primary-light);color:var(--primary);font-size:18px}.stat-card strong{display:block;font-size:1.35rem}.stat-card span:last-child{display:block;color:var(--muted);font-size:.8rem;margin-top:2px}.c2{border-left-color:#15803d}.c3{border-left-color:#2563eb}.c4{border-left-color:#9333ea}.c5{border-left-color:#d97706}.c6{border-left-color:#dc2626}.c7{border-left-color:#0891b2}.c8{border-left-color:#0f766e}
.tabs{display:flex;gap:5px;border-bottom:1px solid var(--border);margin:0 0 18px;overflow-x:auto}.tab{border:0;background:transparent;padding:10px 13px;font-weight:800;color:var(--muted);border-bottom:3px solid transparent;border-radius:9px 9px 0 0;cursor:pointer;white-space:nowrap}.tab:hover,.tab.active{background:var(--primary-soft);color:var(--primary)}.tab.active{border-color:var(--primary)}.tab-count{display:inline-flex;min-width:20px;height:20px;padding:0 6px;margin-left:4px;align-items:center;justify-content:center;border-radius:999px;background:var(--primary-light);color:var(--primary);font-size:.7rem}.tab-panel{display:none}.tab-panel.active{display:block}.dashboard-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.dashboard-panel{padding:18px}.panel-title{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-title h2{font-size:1.1rem;margin:2px 0 0}.panel-title>a:not(.btn){font-weight:800;color:var(--primary);font-size:.86rem}.profile-summary{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:0}.profile-summary div{background:var(--surface-soft);padding:12px;border:1px solid var(--border);border-radius:10px}.profile-summary dt{font-size:.75rem;color:var(--muted);font-weight:800}.profile-summary dd{margin:3px 0 0;font-weight:800}.theme-title{margin:2px 0 8px}.theme-scripture{font-weight:800;color:var(--primary)}.shortcut-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:15px}.shortcut-row a{padding:9px 11px;background:var(--primary-soft);color:var(--primary);border-radius:9px;font-weight:800}.stack-list{display:flex;flex-direction:column;gap:8px}.list-row{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:11px;padding:10px;border:1px solid var(--border);border-radius:10px;background:#fff}.list-row:hover{border-color:#c9bee0;background:#fbfaff}.list-row strong,.list-row small{display:block}.list-row small{font-size:.78rem;color:var(--muted);margin-top:2px}.list-icon{width:36px;height:36px;border-radius:9px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}.unread-row{border-left:3px solid var(--primary)}.unread-dot{width:9px;height:9px;background:var(--primary);border-radius:50%}.hidden-form{display:none}.empty-state{text-align:center;padding:28px 16px;color:var(--muted)}.empty-state>i{font-size:1.7rem;color:var(--primary);margin-bottom:8px}.empty-state h3{margin:0 0 5px;color:var(--text)}.empty-state p{margin:0}.dashboard-pagination{margin-top:16px;padding-top:14px;border-top:1px solid var(--border)}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-filters{grid-template-columns:1fr 170px auto}}@media(max-width:800px){.dashboard-grid{grid-template-columns:1fr}.dashboard-filters{grid-template-columns:1fr 1fr}.dashboard-filters .search-field{grid-column:1/-1}}@media(max-width:520px){.stats-grid,.profile-summary,.dashboard-filters{grid-template-columns:1fr}.dashboard-filters .search-field{grid-column:auto}.dashboard-filters .btn{width:100%}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const hiddenTab=document.getElementById('dashboardActiveTab');
    document.querySelectorAll('.tabs .tab').forEach(btn=>btn.addEventListener('click',()=>{
        document.querySelectorAll('.tabs .tab').forEach(b=>b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-'+btn.dataset.tab)?.classList.add('active');
        if(hiddenTab) hiddenTab.value=btn.dataset.tab;
        const url=new URL(window.location.href);url.searchParams.set('tab',btn.dataset.tab);history.replaceState({},'',url);
    }));
});
</script>
@endsection
