@php
    try {
        $brand = app(\App\Services\Branding\BrandingService::class)->data();
        $sysName = \App\Models\SiteSetting::get('system_name', $brand['short_name'] ?? 'COU Youth Platform');
        $sysLogoUrl = $brand['logo_url'] ?? null;
        $primary = $brand['primary_color'] ?? '#4B2E83';
        $secondary = $brand['secondary_color'] ?? '#204F78';
    } catch (\Throwable $e) {
        $brand = ['short_name' => 'COU Youth Platform', 'tagline' => 'Connect, Grow, Serve'];
        $sysName = 'COU Youth Platform';
        $sysLogoUrl = null;
        $primary = '#4B2E83';
        $secondary = '#204F78';
    }

    $me = auth()->user();
    $firstName = \Illuminate\Support\Str::of($me->name)->trim()->explode(' ')->first() ?: $me->name;
    $initials = \Illuminate\Support\Str::of($me->name)->trim()->explode(' ')->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

    try {
        $unreadCount = \App\Models\PlatformNotificationReceipt::query()->where('user_id', $me->id)->whereNull('read_at')->count();
    } catch (\Throwable $e) {
        $unreadCount = 0;
    }

    $nav = [
        null => [
            ['youth.dashboard', 'youth.dashboard', 'fa-house', 'Home'],
        ],
        'Grow' => [
            ['youth.learning', 'youth.learning', 'fa-book-open', 'My learning'],
            ['youth.certificates', 'youth.certificates', 'fa-award', 'Certificates'],
            ['youth.annual-theme', 'youth.annual-theme', 'fa-book-bible', "This year's theme"],
        ],
        'Together' => [
            ['youth.events', 'youth.events*', 'fa-ticket', 'My events'],
            ['youth.calendar', 'youth.calendar', 'fa-calendar-days', 'Calendar'],
            ['youth.life-groups', 'youth.life-groups*', 'fa-people-group', 'Life groups'],
            ['public.prayer', 'public.prayer*', 'fa-hands-praying', 'Prayer'],
        ],
        'Watch & give' => [
            ['youth.media', 'youth.media', 'fa-circle-play', 'Watch & listen'],
            ['youth.donations', 'youth.donations*', 'fa-hand-holding-heart', 'Give'],
        ],
        'You' => [
            ['youth.profile', 'youth.profile*', 'fa-user', 'Profile'],
            ['youth.notifications', 'youth.notifications', 'fa-bell', 'Updates'],
            ['youth.notification-preferences', 'youth.notification-preferences*', 'fa-sliders', 'Settings'],
        ],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ app(\App\Services\Branding\AppearanceService::class)->data()['background_color'] }}">
    <title>@yield('title', 'COU Youth')</title>
    <script>try{if(localStorage.getItem('cou.sidebar')==='collapsed')document.documentElement.classList.add('side-collapsed')}catch(e){}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root{--primary:{{ $primary }};--secondary:{{ $secondary }};--shadow-sm:none;--shadow-md:none}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%}a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}
        .top{height:var(--top-h);background:color-mix(in srgb,var(--paper) 92%,transparent);backdrop-filter:blur(12px);border-bottom:1.5px solid var(--line);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .top-left{display:flex;align-items:center;gap:10px;min-width:0}
        .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-weight:800;font-size:1.05rem;letter-spacing:-.02em;color:var(--ink);min-width:0;max-width:min(26rem,45vw)}
        .brand-logo-shell{width:42px;height:42px;min-width:42px;border:var(--bw) solid var(--edge);border-radius:13px;background:#fff;display:grid;place-items:center;overflow:hidden;color:var(--primary)}
        .brand-logo{width:100%;height:100%;object-fit:contain;padding:3px}.brand-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .top-actions{display:flex;align-items:center;gap:8px}
        .bell{position:relative;display:grid;place-items:center;width:44px;height:44px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink);transition:.15s ease}
        .bell:hover{border-color:var(--edge-strong)}
        .bell .dot{position:absolute;top:-4px;right:-4px;min-width:21px;height:21px;padding:0 5px;border-radius:999px;background:var(--coral);color:#fff;font-size:.7rem;font-weight:800;display:grid;place-items:center;border:2px solid var(--paper)}
        .me-chip{display:inline-flex;align-items:center;gap:9px;padding:4px 14px 4px 4px;border-radius:999px;border:1.5px solid var(--line);background:#fff;font-weight:700;color:var(--ink);transition:.15s ease}
        .me-chip:hover{border-color:var(--edge-strong)}
        .avatar{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:var(--sun);color:var(--ink);font-family:var(--font-display);font-weight:800;font-size:.85rem;border:var(--bw) solid var(--edge)}
        .logout-btn{width:44px;height:44px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink-soft);cursor:pointer;display:grid;place-items:center}
        .logout-btn:hover{border-color:var(--edge-strong);color:var(--ink)}
        .mobile-menu-btn{display:none}

        .wrap{display:grid}
        .side{background:var(--side-bg);color:var(--side-ink);border-right:1.5px solid color-mix(in srgb,var(--side-ink) 12%,transparent);padding:14px 14px 28px;position:sticky;overflow:auto;overflow-x:hidden;scrollbar-width:thin;transition:padding .2s ease}
        .side-brand{display:none}
        .nav-label{font-size:.78rem;font-weight:700;color:color-mix(in srgb,var(--side-ink) 60%,transparent);padding:16px 12px 6px;white-space:nowrap}
        .side a{position:relative;display:flex;align-items:center;gap:12px;width:100%;padding:10px 12px;margin:2px 0;border:1.5px solid transparent;border-radius:14px;color:color-mix(in srgb,var(--side-ink) 82%,transparent);font-weight:600;font-size:.95rem;white-space:nowrap;transition:background .15s ease,color .15s ease,border-color .15s ease}
        .side a i{width:20px;flex:0 0 20px;text-align:center;color:color-mix(in srgb,var(--side-ink) 60%,transparent);transition:color .15s ease}
        .side a:hover{background:color-mix(in srgb,var(--side-ink) 9%,transparent);color:var(--side-ink)}
        .side a:hover i{color:var(--side-ink)}
        .side a.active{background:#fff;border-color:var(--edge-strong);color:var(--ink);font-weight:750;box-shadow:var(--pop)}
        .side a.active i{color:var(--primary)}
        .side .count{margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:var(--coral);color:#fff;font-size:.72rem;font-weight:800;display:grid;place-items:center}
        .side-foot{margin:22px 4px 0;padding:14px;border-radius:16px;background:#fff;border:1.5px dashed var(--line);font-size:.86rem;color:var(--ink-soft)}
        .side-foot a{display:inline-flex;width:auto;padding:0;margin:6px 0 0;border:0;color:var(--primary);font-weight:700;background:none!important;box-shadow:none!important}
        .side-foot a i{color:var(--primary)}
        .side-toggle{display:flex;align-items:center;gap:12px;width:100%;margin:0 0 6px;padding:9px 12px;border:1.5px dashed color-mix(in srgb,var(--side-ink) 22%,transparent);border-radius:14px;background:transparent;color:color-mix(in srgb,var(--side-ink) 70%,transparent);font-weight:650;font-size:.85rem;cursor:pointer;white-space:nowrap}
        .side-toggle i{width:20px;flex:0 0 20px;text-align:center;transition:transform .2s ease}
        .side-toggle:hover{color:var(--side-ink);border-style:solid}
        /* Collapsed: an icon rail on larger screens. Labels stay readable to screen readers. */
        @media(min-width:801px){
            html.side-collapsed{--side-w:4.75rem}
            .side-collapsed .side{padding-inline:10px}
            .side-collapsed .side .label{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
            .side-collapsed .side a,.side-collapsed .side-toggle{justify-content:center;padding-inline:0;gap:0}
            .side-collapsed .side-toggle i{transform:rotate(180deg)}
            .side-collapsed .nav-label{height:1px;padding:0;margin:12px 8px;overflow:hidden;color:transparent;background:color-mix(in srgb,var(--side-ink) 15%,transparent)}
            .side-collapsed .side-foot{display:none}
            .side-collapsed .side .count{position:absolute;top:4px;right:6px;min-width:18px;height:18px;font-size:.62rem;padding:0 4px}
        }
        @media(max-width:800px){.side-toggle{display:none}}

        .main{padding-block:28px 48px;min-width:0}
        .flash{padding:13px 16px;margin-bottom:20px}
        /* Defaults older pages (giving, prayer) still rely on. */
        .card{padding:18px}.btn-wide{width:100%}.pagination{margin-top:20px}
        body .btn.btn-light{border-color:var(--line);color:var(--ink)}

        .tabbar{display:none}
        @media(max-width:800px){
            .wrap{grid-template-columns:1fr}.side{display:none}
            .side.open{display:block;position:fixed;top:var(--top-h);left:0;bottom:0;width:min(var(--side-w),calc(100vw - 32px));height:calc(100dvh - var(--top-h));z-index:90}
            .mobile-menu-btn{display:inline-flex;align-items:center;justify-content:center}
            .main{padding-block:18px calc(96px + env(safe-area-inset-bottom))}
            .me-chip{display:none}.brand{max-width:52vw}
            .branded-menu-toggle{border:1.5px solid var(--line)!important;border-radius:14px!important;color:var(--ink)!important}
            .tabbar{display:grid;grid-template-columns:repeat(5,1fr);position:fixed;left:10px;right:10px;bottom:calc(10px + env(safe-area-inset-bottom));z-index:95;background:var(--ink);border-radius:22px;padding:6px;box-shadow:0 10px 30px rgba(29,21,48,.25)}
            .tabbar a{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;min-height:52px;border-radius:16px;color:rgba(255,255,255,.7);font-size:.7rem;font-weight:700}
            .tabbar a i{font-size:1.1rem}
            .tabbar a.active{background:var(--sun);color:var(--ink)}
            body.mobile-nav-open .tabbar{display:none}
        }
    </style>
    @include('partials.platform-layout-styles')
    @include('partials.form-control-styles')
    @include('partials.youth-theme')
</head>
<body>
<div class="top">
    <div class="top-left">
        <x-mobile-menu-button :title="$sysName" :logo="$sysLogoUrl" controls="portalSidebar" />
        <a class="brand" href="{{ route('youth.dashboard') }}">
            @if(!empty($sysLogoUrl))<span class="brand-logo-shell"><img class="brand-logo" src="{{ $sysLogoUrl }}" alt="{{ $sysName }} logo"></span>@else<span class="brand-logo-shell"><i class="fas fa-church"></i></span>@endif
            <span class="brand-text">{{ $sysName }}</span>
        </a>
    </div>
    <div class="top-actions">
        @include('partials.header-search')
        <a class="bell" href="{{ route('youth.notifications') }}" aria-label="Updates{{ $unreadCount ? ', '.$unreadCount.' new' : '' }}">
            <i class="fas fa-bell" aria-hidden="true"></i>
            @if($unreadCount)<span class="dot" aria-hidden="true">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
        </a>
        <a class="me-chip" href="{{ route('youth.profile') }}" title="Your profile">
            <span class="avatar" aria-hidden="true">@if($initials){{ $initials }}@else<i class="fas fa-user"></i>@endif</span>
            <span>{{ $me->name }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-btn" type="submit" aria-label="Log out" title="Log out"><i class="fas fa-right-from-bracket" aria-hidden="true"></i></button></form>
    </div>
</div>
<div class="wrap">
    <aside class="side" id="portalSidebar" aria-label="Youth portal navigation">
        <button class="side-toggle" type="button" data-side-toggle aria-expanded="true" aria-controls="portalSidebar"><i class="fas fa-angles-left" aria-hidden="true"></i><span class="label">Collapse menu</span></button>
        @foreach($nav as $group => $links)
            @if($group)<div class="nav-label">{{ $group }}</div>@endif
            @foreach($links as [$route, $pattern, $icon, $label])
                <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}" data-tip="{{ $label }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                    <i class="fas {{ $icon }}" aria-hidden="true"></i> <span class="label">{{ $label }}</span>
                    @if($route === 'youth.notifications' && $unreadCount)<span class="count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
                </a>
            @endforeach
        @endforeach
        <div class="side-foot">
            Looking for something else?
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-arrow-up-right-from-square"></i>&nbsp;Main website</a>
            @if(!empty($brand['support_email']))<br><a href="mailto:{{ $brand['support_email'] }}"><i class="fas fa-envelope"></i>&nbsp;Message the team</a>@endif
        </div>
    </aside>
    <div class="mobile-nav-backdrop" data-mobile-nav-backdrop aria-hidden="true"></div>
    <div class="portal-content">
        <main class="main" id="main-content">@if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif @yield('youth_content')</main>
    </div>
</div>
<nav class="tabbar" aria-label="Quick navigation">
    @foreach([['youth.dashboard','youth.dashboard','fa-house','Home'],['youth.learning','youth.learning','fa-book-open','Learn'],['youth.events','youth.events*','fa-ticket','Events'],['youth.life-groups','youth.life-groups*','fa-people-group','Groups'],['youth.profile','youth.profile*','fa-user','Me']] as [$route, $pattern, $icon, $label])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif><i class="fas {{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
    @endforeach
</nav>
@include('partials.mobile-navigation')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-side-toggle]');
    if (!toggle) return;
    const root = document.documentElement;
    const apply = collapsed => {
        root.classList.toggle('side-collapsed', collapsed);
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.querySelector('.label').textContent = collapsed ? 'Expand menu' : 'Collapse menu';
        toggle.title = collapsed ? 'Expand menu' : 'Collapse menu';
        // Tooltips only matter when the labels are hidden.
        document.querySelectorAll('#portalSidebar a[data-tip]').forEach(link => {
            if (collapsed) link.title = link.dataset.tip; else link.removeAttribute('title');
        });
    };
    apply(root.classList.contains('side-collapsed'));
    toggle.addEventListener('click', () => {
        const collapsed = !root.classList.contains('side-collapsed');
        apply(collapsed);
        try { localStorage.setItem('cou.sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
    });
});
</script>
</body>
</html>
