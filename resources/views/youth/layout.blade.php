@php
    try {
        $brand = app(\App\Services\Branding\BrandingService::class)->data();
        $sysName = \App\Models\SiteSetting::get('system_name', $brand['short_name'] ?? 'COU Youth Platform');
        $sysLogoUrl = $brand['logo_url'] ?? null;
        $primary = $brand['primary_color'] ?? '#4B2E83';
        $secondary = $brand['secondary_color'] ?? '#204F78';
    } catch (\Throwable $e) {
        $brand = ['short_name' => 'COU Youth Platform', 'tagline' => 'Connect • Grow • Serve'];
        $sysName = 'COU Youth Platform';
        $sysLogoUrl = null;
        $primary = '#4B2E83';
        $secondary = '#204F78';
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COU Youth Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root{--primary:{{ $primary }};--primary-light:color-mix(in srgb, {{ $primary }} 16%, #fff 84%);--primary-soft:color-mix(in srgb, {{ $primary }} 9%, #fff 91%);--secondary:{{ $secondary }};--bg:#f6f7fb;--surface:#fff;--surface-soft:#fbfaff;--border:#e7e9f0;--text:#182230;--muted:#667085;--shadow-sm:0 8px 28px rgba(16,24,40,.05);--shadow-md:0 12px 34px rgba(16,24,40,.07)}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:'DM Sans',Arial,sans-serif;background:linear-gradient(180deg,#f8f9fd 0,#f4f6fb 100%);color:var(--text)}a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}
        .top{height:68px;background:rgba(255,255,255,.96);backdrop-filter:blur(12px);padding:0 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}.top-left{display:flex;align-items:center;gap:10px;min-width:0}.brand{display:flex;align-items:center;gap:10px;font-weight:800;color:var(--primary);min-width:0;max-width:min(430px,45vw)}.brand-logo-shell{width:42px;height:42px;min-width:42px;border:1px solid var(--border);border-radius:12px;background:#fff;display:grid;place-items:center;overflow:hidden;box-shadow:0 5px 18px rgba(16,24,40,.08)}.brand-logo{width:100%;height:100%;object-fit:contain;padding:3px}.brand-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.top-actions{display:flex;align-items:center;gap:10px}.user-name{font-weight:700;color:#344054}.icon-btn{border:1px solid var(--border);background:#fff;width:38px;height:38px;border-radius:10px;cursor:pointer;color:#475467}.mobile-menu-btn{display:none}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid transparent;border-radius:10px;padding:9px 14px;font-weight:700;cursor:pointer;background:#fff;transition:.18s ease}.btn:hover{transform:translateY(-1px)}.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--secondary)}.btn-light{border-color:var(--border);color:#344054}.btn-wide{width:100%}
        .wrap{display:grid;grid-template-columns:270px minmax(0,1fr);min-height:calc(100vh - 68px)}.side{background:var(--primary);padding:16px 13px 22px;position:sticky;top:68px;height:calc(100vh - 68px);overflow:auto;color:#fff;box-shadow:10px 0 35px rgba(30,20,65,.08);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.3) transparent}.side-brand{display:flex;align-items:center;gap:10px;margin:0 3px 12px;padding:11px;border:1px solid rgba(255,255,255,.2);border-radius:14px;background:rgba(255,255,255,.1);min-width:0}.side-brand .brand-logo-shell{width:40px;height:40px;min-width:40px;box-shadow:none;border-color:rgba(255,255,255,.2)}.side-brand-copy{min-width:0}.side-brand-copy strong,.side-brand-copy small{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#fff}.side-brand-copy strong{font-size:13px}.side-brand-copy small{font-size:11px;margin-top:2px;opacity:.78}.nav-label{font-size:10px;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.11em;padding:15px 12px 6px}.side a,.side button{display:flex;align-items:center;gap:11px;width:100%;padding:10px 12px;margin:2px 0;border:0;border-radius:10px;color:#fff;background:transparent;font:inherit;font-weight:600;font-size:14px;text-align:left;cursor:pointer;transition:.18s ease}.side a i,.side button i{width:20px;text-align:center;color:#fff;font-size:15px;opacity:.9}.side a:hover,.side a.active,.side button:hover{background:var(--primary-light);color:var(--primary);transform:translateX(2px)}.side a:hover i,.side a.active i,.side button:hover i{color:var(--primary);opacity:1}.side form{margin:0}
        .main{padding:25px;min-width:0}.flash{padding:13px 15px;border-radius:11px;margin-bottom:16px;background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}.hero{padding:6px 0 24px}.hero h1{font-size:clamp(2rem,4vw,3rem);line-height:1.1;margin:0 0 10px;color:#171225}.hero p{color:var(--muted);font-size:1.02rem;max-width:760px;margin:0}.card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow-sm)}.card:hover{box-shadow:var(--shadow-md)}.muted{color:var(--muted)}.badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:var(--primary-light);color:var(--primary);font-size:12px;font-weight:800}.pagination{margin-top:20px}.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}
        .page-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:0 0 20px}.page-stat{display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:16px;padding:15px;box-shadow:var(--shadow-sm);min-width:0}.page-stat:nth-child(4n+2){border-left-color:#15803d}.page-stat:nth-child(4n+3){border-left-color:#2563eb}.page-stat:nth-child(4n+4){border-left-color:#9333ea}.page-stat-icon{width:44px;height:44px;min-width:44px;border-radius:11px;display:grid;place-items:center;background:var(--primary-light);color:var(--primary);font-size:17px}.page-stat strong{display:block;font-size:1.3rem;line-height:1.1}.page-stat span{display:block;color:var(--muted);font-size:.78rem;margin-top:3px}.side-backdrop{display:none}
        @media(max-width:1100px){.page-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:800px){.wrap{grid-template-columns:1fr}.side{display:none}.side.open{display:block;position:fixed;top:68px;left:0;bottom:0;width:270px;height:calc(100vh - 68px);z-index:90;box-shadow:18px 0 45px rgba(0,0,0,.25)}.mobile-menu-btn{display:inline-flex;align-items:center;justify-content:center}.main{padding:16px}.top{padding:0 14px}.top .user-name{display:none}.brand{max-width:52vw}.brand-text{max-width:145px}.side-backdrop.open{display:block;position:fixed;inset:68px 0 0;background:rgba(15,23,42,.5);z-index:80}}
        @media(max-width:560px){.page-stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="top">
    <div class="top-left">
        <button class="icon-btn mobile-menu-btn" id="menuToggle" type="button" aria-label="Open portal navigation"><i class="fas fa-bars"></i></button>
        <a class="brand" href="{{ route('youth.dashboard') }}">
            @if(!empty($sysLogoUrl))<span class="brand-logo-shell"><img class="brand-logo" src="{{ $sysLogoUrl }}" alt="{{ $sysName }} logo"></span>@else<span class="brand-logo-shell"><i class="fas fa-church" style="color:var(--primary)"></i></span>@endif
            <span class="brand-text">{{ $sysName }}</span>
        </a>
    </div>
    <div class="top-actions">
        <span class="user-name">Welcome, {{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light" type="submit"><i class="fas fa-right-from-bracket"></i> Logout</button></form>
    </div>
</div>
<div class="wrap">
    <aside class="side" id="portalSidebar" aria-label="Youth portal navigation">
        <div class="side-brand">@if(!empty($sysLogoUrl))<span class="brand-logo-shell"><img class="brand-logo" src="{{ $sysLogoUrl }}" alt=""></span>@else<span class="brand-logo-shell"><i class="fas fa-church" style="color:var(--primary)"></i></span>@endif<div class="side-brand-copy"><strong>{{ $brand['short_name'] ?? 'COU Youth Platform' }}</strong><small>{{ $brand['tagline'] ?? 'Connect • Grow • Serve' }}</small></div></div>
        <div class="nav-label">Overview</div>
        <a href="{{ route('youth.dashboard') }}" class="{{ request()->routeIs('youth.dashboard') ? 'active' : '' }}"><i class="fas fa-gauge-high"></i> Dashboard</a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-arrow-up-right-from-square"></i> Public Website</a>
        <div class="nav-label">My Account</div>
        <a href="{{ route('youth.profile') }}" class="{{ request()->routeIs('youth.profile*') ? 'active' : '' }}"><i class="fas fa-user-pen"></i> My Profile</a>
        <a href="{{ route('youth.notifications') }}" class="{{ request()->routeIs('youth.notifications*') ? 'active' : '' }}"><i class="fas fa-bell"></i> Notifications</a>
        <a href="{{ route('youth.notification-preferences') }}" class="{{ request()->routeIs('youth.notification-preferences*') ? 'active' : '' }}"><i class="fas fa-sliders"></i> Preferences</a>
        <div class="nav-label">Growth & Community</div>
        <a href="{{ route('youth.learning') }}" class="{{ request()->routeIs('youth.learning') ? 'active' : '' }}"><i class="fas fa-graduation-cap"></i> My Learning</a>
        <a href="{{ route('youth.life-groups') }}" class="{{ request()->routeIs('youth.life-groups*') ? 'active' : '' }}"><i class="fas fa-people-group"></i> Life Groups</a>
        <a href="{{ route('youth.events') }}" class="{{ request()->routeIs('youth.events*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i> My Events</a>
        <a href="{{ route('youth.calendar') }}" class="{{ request()->routeIs('youth.calendar') ? 'active' : '' }}"><i class="fas fa-calendar-days"></i> Calendar</a>
        <a href="{{ route('youth.certificates') }}" class="{{ request()->routeIs('youth.certificates') ? 'active' : '' }}"><i class="fas fa-certificate"></i> Certificates</a>
        <a href="{{ route('public.prayer') }}" class="{{ request()->routeIs('public.prayer*') ? 'active' : '' }}"><i class="fas fa-hands-praying"></i> Prayer Support</a>
        <div class="nav-label">Resources</div>
        <a href="{{ route('youth.annual-theme') }}" class="{{ request()->routeIs('youth.annual-theme') ? 'active' : '' }}"><i class="fas fa-book-bible"></i> Annual Theme</a>
        <a href="{{ route('youth.media') }}" class="{{ request()->routeIs('youth.media') ? 'active' : '' }}"><i class="fas fa-photo-film"></i> Media & Services</a>
        <a href="{{ route('public.donate') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-hand-holding-heart"></i> Donate</a>
    </aside>
    <div class="side-backdrop" id="sideBackdrop"></div>
    <main class="main" id="main-content">@if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif @yield('youth_content')</main>
</div>
<script>
document.addEventListener('DOMContentLoaded',()=>{const btn=document.getElementById('menuToggle');const side=document.getElementById('portalSidebar');const backdrop=document.getElementById('sideBackdrop');if(!btn||!side||!backdrop)return;const close=()=>{side.classList.remove('open');backdrop.classList.remove('open')};btn.addEventListener('click',()=>{side.classList.toggle('open');backdrop.classList.toggle('open')});backdrop.addEventListener('click',close);side.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));window.addEventListener('resize',()=>{if(window.innerWidth>800)close()});});
</script>
</body>
</html>
