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
            ['youth.annual-theme', 'youth.annual-theme', 'fa-book-bible', 'This year’s theme'],
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
    <meta name="theme-color" content="#fbf6ee">
    <title>@yield('title', 'COU Youth')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root{--primary:{{ $primary }};--secondary:{{ $secondary }};--shadow-sm:none;--shadow-md:none}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%}a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}
        .top{height:var(--top-h);background:color-mix(in srgb,var(--paper) 92%,transparent);backdrop-filter:blur(12px);border-bottom:1.5px solid var(--line);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .top-left{display:flex;align-items:center;gap:10px;min-width:0}
        .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-weight:800;font-size:1.05rem;letter-spacing:-.02em;color:var(--ink);min-width:0;max-width:min(26rem,45vw)}
        .brand-logo-shell{width:42px;height:42px;min-width:42px;border:1.5px solid var(--ink);border-radius:13px;background:#fff;display:grid;place-items:center;overflow:hidden;color:var(--primary)}
        .brand-logo{width:100%;height:100%;object-fit:contain;padding:3px}.brand-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .top-actions{display:flex;align-items:center;gap:8px}
        .bell{position:relative;display:grid;place-items:center;width:44px;height:44px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink);transition:.15s ease}
        .bell:hover{border-color:var(--ink)}
        .bell .dot{position:absolute;top:-4px;right:-4px;min-width:21px;height:21px;padding:0 5px;border-radius:999px;background:var(--coral);color:#fff;font-size:.7rem;font-weight:800;display:grid;place-items:center;border:2px solid var(--paper)}
        .me-chip{display:inline-flex;align-items:center;gap:9px;padding:4px 14px 4px 4px;border-radius:999px;border:1.5px solid var(--line);background:#fff;font-weight:700;color:var(--ink);transition:.15s ease}
        .me-chip:hover{border-color:var(--ink)}
        .avatar{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:var(--sun);color:var(--ink);font-family:var(--font-display);font-weight:800;font-size:.85rem;border:1.5px solid var(--ink)}
        .logout-btn{width:44px;height:44px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink-soft);cursor:pointer;display:grid;place-items:center}
        .logout-btn:hover{border-color:var(--ink);color:var(--ink)}
        .mobile-menu-btn{display:none}

        .wrap{display:grid}
        .side{background:var(--paper-2);border-right:1.5px solid var(--line);padding:18px 14px 28px;position:sticky;overflow:auto;scrollbar-width:thin}
        .side-brand{display:none}
        .nav-label{font-size:.78rem;font-weight:700;color:var(--muted);padding:16px 12px 6px}
        .side a{display:flex;align-items:center;gap:12px;width:100%;padding:10px 12px;margin:2px 0;border:1.5px solid transparent;border-radius:14px;color:var(--ink-soft);font-weight:600;font-size:.95rem;transition:background .15s ease,color .15s ease,border-color .15s ease}
        .side a i{width:20px;text-align:center;color:var(--muted);transition:color .15s ease}
        .side a:hover{background:color-mix(in srgb,#fff 70%,transparent);color:var(--ink)}
        .side a:hover i{color:var(--primary)}
        .side a.active{background:#fff;border-color:var(--ink);color:var(--ink);font-weight:750;box-shadow:3px 3px 0 var(--ink)}
        .side a.active i{color:var(--primary)}
        .side .count{margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:var(--coral);color:#fff;font-size:.72rem;font-weight:800;display:grid;place-items:center}
        .side-foot{margin:22px 4px 0;padding:14px;border-radius:16px;background:#fff;border:1.5px dashed var(--line);font-size:.86rem;color:var(--ink-soft)}
        .side-foot a{display:inline-flex;width:auto;padding:0;margin:6px 0 0;border:0;color:var(--primary);font-weight:700;background:none!important;box-shadow:none!important}
        .side-foot a i{color:var(--primary)}

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
            <span class="avatar" aria-hidden="true">{{ $initials ?: '🙂' }}</span>
            <span>{{ $me->name }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-btn" type="submit" aria-label="Log out" title="Log out"><i class="fas fa-right-from-bracket" aria-hidden="true"></i></button></form>
    </div>
</div>
<div class="wrap">
    <aside class="side" id="portalSidebar" aria-label="Youth portal navigation">
        @foreach($nav as $group => $links)
            @if($group)<div class="nav-label">{{ $group }}</div>@endif
            @foreach($links as [$route, $pattern, $icon, $label])
                <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                    <i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }}
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
</body>
</html>
