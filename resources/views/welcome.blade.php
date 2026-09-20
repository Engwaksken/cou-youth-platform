<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connecting Young People. Growing Disciples. Transforming Nations.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Church of Uganda Youth Platform</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root{--primary:#4b2e83;--primary-dark:#35205f;--secondary:#204f78;--accent:#d4af37;--background:#f8fafc;--surface:#fff;--text:#1f2937;--muted:#6b7280;--border:#e5e7eb}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--background);color:var(--text);font-family:Arial,Helvetica,sans-serif}a{color:inherit;text-decoration:none}.container{width:min(1180px,calc(100% - 32px));margin:0 auto}
        header{position:sticky;top:0;z-index:100;background:var(--primary);color:#fff;box-shadow:0 3px 12px rgba(0,0,0,.12)}.navbar{min-height:72px;display:flex;align-items:center;justify-content:space-between;gap:24px}.brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:20px;font-weight:800}.brand i{color:var(--accent);font-size:24px}.nav-links{display:flex;align-items:center;gap:22px}.nav-links a{color:#fff;font-weight:600}.nav-links a:hover{color:#fde68a}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border:0;border-radius:10px;font-weight:700;cursor:pointer;transition:.2s ease}.btn:hover{transform:translateY(-1px)}.btn-light{background:#fff;color:var(--primary)}.btn-primary{background:var(--primary);color:#fff}.btn-outline{border:1px solid var(--primary);color:var(--primary);background:transparent}
        .hero{padding:100px 0;color:#fff;background:linear-gradient(135deg,rgba(75,46,131,.97),rgba(32,79,120,.94))}.hero-content{max-width:850px;margin:auto;text-align:center}.hero-icon{width:78px;height:78px;margin:0 auto 22px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.12);font-size:36px;color:var(--accent)}.hero h1{margin:0 0 18px;font-size:clamp(38px,6vw,62px);line-height:1.08}.tagline{margin-bottom:18px;font-size:clamp(19px,3vw,27px);font-weight:700}.hero p{max-width:760px;margin:0 auto 30px;color:#e5e7eb;font-size:18px;line-height:1.7}.hero-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px}.hero .btn-primary{background:#fff;color:var(--primary)}.hero .btn-outline{color:#fff;border-color:rgba(255,255,255,.8)}
        section{padding:72px 0}.section-head{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:30px}.section-head h2{margin:0 0 8px;font-size:31px}.section-head p{margin:0;color:var(--muted)}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.module-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 8px 25px rgba(15,23,42,.05)}.card-icon{width:50px;height:50px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;border-radius:13px;background:#ede9fe;color:var(--primary);font-size:22px}.card h3{margin:0 0 10px;font-size:20px}.card p{margin:0;color:var(--muted);line-height:1.6}.card-meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;color:var(--muted);font-size:14px}.card-meta span{display:inline-flex;align-items:center;gap:6px}
        .statistics{margin-top:-45px;position:relative;z-index:2}.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.stat-card{background:#fff;border-radius:15px;padding:22px;display:flex;align-items:center;gap:16px;box-shadow:0 10px 30px rgba(0,0,0,.09)}.stat-icon{width:50px;height:50px;border-radius:12px;background:#ede9fe;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:21px}.stat-number{font-size:26px;font-weight:800}.stat-label{color:var(--muted);font-size:14px}
        .empty-state{grid-column:1/-1;padding:35px;border:1px dashed var(--border);border-radius:14px;text-align:center;color:var(--muted);background:#fff}.empty-state i{display:block;margin-bottom:12px;color:var(--primary);font-size:32px}.cta{background:#111827;color:#fff}.cta-content{max-width:820px;margin:auto;text-align:center}.cta h2{margin-top:0;font-size:34px}.cta p{color:#d1d5db;line-height:1.7}footer{padding:30px 0;background:#0f172a;color:#cbd5e1;text-align:center}
        .accessibility{position:fixed;right:18px;bottom:18px;z-index:200}.accessibility button{width:54px;height:54px;border:0;border-radius:50%;background:var(--primary);color:#fff;font-size:21px;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.2)}body.high-contrast{background:#000;color:#fff}body.high-contrast .card,body.high-contrast .stat-card{background:#000;color:#fff;border-color:#fff}body.high-contrast .card p,body.high-contrast .card-meta,body.high-contrast .stat-label{color:#fff}
        @media(max-width:1000px){.module-grid,.stats-grid,.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.nav-links{display:none}.grid,.module-grid,.stats-grid{grid-template-columns:1fr}.section-head{align-items:flex-start;flex-direction:column}.hero{padding:75px 0 90px}}
    </style>
</head>
<body>
<header>
    <div class="container navbar">
        <a href="{{ route('home') }}" class="brand" aria-label="Church of Uganda Youth Platform Home"><i class="fas fa-church"></i><span>COU Youth Platform</span></a>
        <nav class="nav-links" aria-label="Main navigation"><a href="#about">About</a><a href="#events">Events</a><a href="#discipleship">Discipleship</a><a href="#news">News</a><a href="#churches">Church Locator</a></nav>
        @auth
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light"><i class="fas fa-gauge-high"></i> Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-light"><i class="fas fa-right-to-bracket"></i> Admin Login</a>
        @endauth
    </div>
</header>

<main>
    <section class="hero">
        <div class="container"><div class="hero-content"><div class="hero-icon"><i class="fas fa-cross"></i></div><h1>Church of Uganda Youth Platform</h1><div class="tagline">Connecting Young People. Growing Disciples. Transforming Nations.</div><p>A digital home connecting Church of Uganda young people through discipleship, fellowship, leadership development, missions, opportunities, talents and service.</p><div class="hero-actions"><a href="#about" class="btn btn-primary"><i class="fas fa-compass"></i> Explore Platform</a><a href="#events" class="btn btn-outline"><i class="fas fa-calendar-days"></i> Upcoming Events</a></div></div></div>
    </section>

    <div class="statistics"><div class="container stats-grid">
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-calendar-check"></i></div><div><div class="stat-number">{{ number_format($stats['events'] ?? 0) }}</div><div class="stat-label">Events</div></div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-graduation-cap"></i></div><div><div class="stat-number">{{ number_format($stats['courses'] ?? 0) }}</div><div class="stat-label">Courses</div></div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-people-group"></i></div><div><div class="stat-number">{{ number_format($stats['life_groups'] ?? 0) }}</div><div class="stat-label">Life Groups</div></div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-church"></i></div><div><div class="stat-number">{{ number_format($stats['church_locations'] ?? 0) }}</div><div class="stat-label">Church Locations</div></div></div>
    </div></div>

    <section id="about"><div class="container"><div class="section-head"><div><h2>Explore the Platform</h2><p>Connect, grow, serve and discover opportunities.</p></div></div><div class="module-grid">
        @php
            $modules = [
                ['icon'=>'fa-book-bible','title'=>'Discipleship','text'=>'Bible studies, devotions, courses, quizzes and certificates.'],
                ['icon'=>'fa-calendar-days','title'=>'Events','text'=>'Provincial, Diocesan, Parish and local youth activities.'],
                ['icon'=>'fa-people-group','title'=>'Life Groups','text'=>'Grow through small fellowship and discipleship communities.'],
                ['icon'=>'fa-hands-praying','title'=>'Prayer','text'=>'Prayer requests and safeguarded pastoral support.'],
                ['icon'=>'fa-user-graduate','title'=>'Leadership','text'=>'Leadership development and ministry training.'],
                ['icon'=>'fa-briefcase','title'=>'Opportunities','text'=>'Jobs, internships, scholarships and volunteering.'],
                ['icon'=>'fa-music','title'=>'Talent Hub','text'=>'Music, sports, arts, media, technology and other talents.'],
                ['icon'=>'fa-location-dot','title'=>'Church Locator','text'=>'Find churches, fellowships and youth ministry contacts.'],
            ];
        @endphp
        @foreach($modules as $module)<div class="card"><div class="card-icon"><i class="fas {{ $module['icon'] }}"></i></div><h3>{{ $module['title'] }}</h3><p>{{ $module['text'] }}</p></div>@endforeach
    </div></div></section>

    <section id="events"><div class="container"><div class="section-head"><div><h2>Upcoming Events</h2><p>Latest youth ministry activities.</p></div></div><div class="grid">
        @forelse($upcomingEvents as $event)
            <article class="card"><div class="card-icon"><i class="fas fa-calendar-days"></i></div><h3>{{ $event->title ?? $event->name ?? 'Youth Event' }}</h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''),150) }}</p><div class="card-meta">
                @if(!empty($event->start_date))<span><i class="fas fa-calendar"></i>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</span>@elseif(!empty($event->event_date))<span><i class="fas fa-calendar"></i>{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</span>@endif
                @if(!empty($event->venue))<span><i class="fas fa-location-dot"></i>{{ $event->venue }}</span>@endif
            </div></article>
        @empty
            <div class="empty-state"><i class="fas fa-calendar-xmark"></i>No upcoming events have been published yet.</div>
        @endforelse
    </div></div></section>

    <section id="discipleship"><div class="container"><div class="section-head"><div><h2>Discipleship & Learning</h2><p>Grow spiritually and develop leadership skills.</p></div></div><div class="grid">
        @forelse($courses as $course)
            <article class="card"><div class="card-icon"><i class="fas fa-book-bible"></i></div><h3>{{ $course->title ?? $course->name ?? 'Discipleship Course' }}</h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($course->description ?? ''),160) }}</p><div class="card-meta"><span><i class="fas fa-graduation-cap"></i>Discipleship</span></div></article>
        @empty
            <div class="empty-state"><i class="fas fa-book-open"></i>Discipleship courses will appear here once published.</div>
        @endforelse
    </div></div></section>

    <section id="news"><div class="container"><div class="section-head"><div><h2>Latest News & Announcements</h2><p>Stay connected with Youth Ministry updates.</p></div></div><div class="grid">
        @forelse($latestNews as $item)
            <article class="card"><div class="card-icon"><i class="fas fa-bullhorn"></i></div><h3>{{ $item->title ?? 'Youth Ministry Update' }}</h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($item->description ?? $item->content ?? $item->body ?? ''),160) }}</p><div class="card-meta"><span><i class="fas fa-clock"></i>{{ optional($item->created_at)->format('d M Y') }}</span></div></article>
        @empty
            <div class="empty-state"><i class="fas fa-newspaper"></i>No news or announcements have been published yet.</div>
        @endforelse
    </div></div></section>

    <section id="churches"><div class="container"><div class="section-head"><div><h2>Church Locator</h2><p>Discover Church of Uganda youth ministry locations.</p></div></div><div class="grid">
        @forelse($churchLocations as $church)
            <article class="card"><div class="card-icon"><i class="fas fa-church"></i></div><h3>{{ $church->name ?? $church->church_name ?? 'Church of Uganda' }}</h3>
                @if(!empty($church->address))<p><i class="fas fa-location-dot"></i> {{ $church->address }}</p>@elseif(!empty($church->location))<p><i class="fas fa-location-dot"></i> {{ $church->location }}</p>@endif
                <div class="card-meta">@if(!empty($church->phone))<span><i class="fas fa-phone"></i>{{ $church->phone }}</span>@endif @if(!empty($church->email))<span><i class="fas fa-envelope"></i>{{ $church->email }}</span>@endif</div>
            </article>
        @empty
            <div class="empty-state"><i class="fas fa-location-crosshairs"></i>Church locations will appear here when added.</div>
        @endforelse
    </div></div></section>

    <section class="cta"><div class="container cta-content"><i class="fas fa-cross" style="font-size:36px;color:#d4af37"></i><h2>Christ-centred Young People Transforming Nations</h2><p>Connecting and equipping young people for spiritual growth, leadership, mission, service and productive lives.</p></div></section>
</main>

<footer><div class="container"><i class="fas fa-church"></i> &copy; {{ date('Y') }} Church of Uganda Youth Platform. All rights reserved.</div></footer>

<div class="accessibility"><button type="button" id="accessibilityButton" aria-label="Accessibility options" title="Accessibility"><i class="fas fa-universal-access"></i></button></div>
<script>document.getElementById('accessibilityButton')?.addEventListener('click',()=>document.body.classList.toggle('high-contrast'));</script>
</body>
</html>
