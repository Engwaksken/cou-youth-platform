@php
    try {
        $platformBrand = app(\App\Services\Branding\BrandingService::class)->data();
    } catch (\Throwable $e) {
        $platformBrand = [
            'name' => 'Church of Uganda Youth Platform',
            'short_name' => 'COU Youth Platform',
            'tagline' => 'Connecting Young People. Growing Disciples. Transforming Nations.',
            'primary_color' => '#4B2E83',
            'secondary_color' => '#204F78',
            'logo_url' => null,
        ];
    }
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $platformBrand['name'])</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
:root{--primary:{{ $platformBrand['primary_color'] ?? '#4b2e83' }};--primary-2:{{ $platformBrand['secondary_color'] ?? '#204f78' }};--dark:#24183f;--bg:#f7f5fb;--surface:#fff;--surface-soft:#fbfaff;--text:#182230;--muted:#667085;--border:#e4e0ec;--border-strong:#d6d0e1;--focus:#ffbf47;--radius:10px;--text-scale:1;--shadow-sm:0 8px 24px rgba(50,35,80,.06);--shadow-md:0 16px 40px rgba(50,35,80,.10)}
*{box-sizing:border-box}
html{scroll-behavior:smooth;font-size:calc(16px * var(--text-scale))}
body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text);line-height:1.6;min-height:100vh}
body::before,body::after{content:"";position:fixed;pointer-events:none;z-index:-1;border-radius:999px;opacity:.45}
body::before{width:280px;height:280px;background:#eee8f8;right:-90px;top:120px}
body::after{width:220px;height:220px;background:#eaf1f8;left:-80px;bottom:100px}
body.high-contrast{--bg:#fff;--surface:#fff;--text:#000;--muted:#222;--border:#000;--primary:#24105c}
body.grayscale{filter:grayscale(1)}body.dyslexia{font-family:Verdana,Tahoma,sans-serif;letter-spacing:.035em;word-spacing:.08em}
body.reduce-motion *,body.reduce-motion *::before,body.reduce-motion *::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}
a{text-decoration:none;color:inherit}
a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid var(--focus);outline-offset:3px}
.skip-link{position:absolute;left:-9999px;top:auto}.skip-link:focus{left:16px;top:12px;z-index:1000;background:#fff;color:#000;padding:10px 14px;border-radius:10px}
.site-header{background:rgba(255,255,255,.97);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:40;backdrop-filter:blur(14px);box-shadow:0 6px 22px rgba(31,41,55,.04)}
.nav{max-width:1180px;margin:auto;padding:13px 20px;display:flex;align-items:center;gap:18px}
.brand{font-weight:900;color:var(--primary);display:flex;align-items:center;gap:10px;margin-right:auto;min-width:0}.brand:hover{color:var(--primary-2)}
.brand-mark{width:44px;height:44px;min-width:44px;border-radius:10px;display:grid;place-items:center;background:#fff;border:1px solid var(--border);overflow:hidden;color:#fff;box-shadow:0 5px 16px rgba(75,46,131,.09)}.brand-mark.brand-fallback{background:var(--primary)}.brand-logo{display:block;width:100%;height:100%;object-fit:contain;padding:3px}.brand-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:230px}
.mobile-menu-toggle{display:none;width:44px;height:44px;min-width:44px;border:1px solid var(--border);border-radius:10px;background:#fff;color:var(--primary);font-size:1.2rem;align-items:center;justify-content:center;cursor:pointer;transition:.2s ease}.mobile-menu-toggle:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
.nav-links{display:flex;align-items:center;gap:4px;flex-wrap:wrap}.nav-links a{font-size:.9rem;font-weight:800;color:#344054;padding:9px 11px;border-radius:10px;transition:background-color .18s ease,color .18s ease,transform .18s ease}.nav-links a:hover,.nav-links a[aria-current="page"]{background:#f0ebf8;color:var(--primary);transform:translateY(-1px)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;border-radius:10px;padding:10px 15px;font-weight:800;border:1px solid var(--border-strong);background:var(--surface);cursor:pointer;color:var(--primary);box-shadow:0 3px 10px rgba(31,41,55,.03);transition:background-color .18s ease,border-color .18s ease,color .18s ease,box-shadow .18s ease,transform .18s ease}.btn:hover,.btn:focus-visible{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:0 8px 20px rgba(75,46,131,.16);transform:translateY(-1px)}.btn:active{transform:translateY(0)}.btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}.btn-primary:hover,.btn-primary:focus-visible{background:var(--primary-2);border-color:var(--primary-2);color:#fff;box-shadow:0 10px 24px rgba(32,79,120,.20)}.btn-icon{width:44px;height:44px;padding:0}
.container{max-width:1180px;margin:auto;padding:34px 20px}.hero{padding:38px 0 26px}.hero h1{font-size:clamp(2rem,5vw,3.35rem);line-height:1.08;margin:0 0 12px;letter-spacing:-.03em;color:#171225}.hero p{color:var(--muted);font-size:1.08rem;max-width:760px;margin-bottom:0}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow-sm);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease}.card:hover{transform:translateY(-4px);border-color:#d7cfea;box-shadow:var(--shadow-md)}.card h3{margin-top:0;color:#201a2d}.card img{border-radius:10px}.muted{color:var(--muted)}
.filters{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;padding:12px;background:rgba(255,255,255,.72);border:1px solid var(--border);border-radius:10px}.filters input,.filters select{flex:1;min-width:200px;border:1px solid #d0d5dd;border-radius:10px;padding:12px 13px;background:var(--surface);color:var(--text);outline:0}.filters input:focus,.filters select:focus,.chat-form input:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(75,46,131,.10)}
.badge{display:inline-flex;border-radius:999px;padding:5px 10px;background:#f0ebf8;color:var(--primary);font-size:.75rem;font-weight:900}.pagination{margin-top:22px}.auth-actions{display:flex;gap:8px;align-items:center}
.flash{padding:13px 15px;border-radius:10px;margin-bottom:18px;background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;box-shadow:var(--shadow-sm)}
.floating-tools{position:fixed;right:18px;bottom:18px;display:flex;flex-direction:column;gap:10px;z-index:60}.float-btn{width:52px;height:52px;border:1px solid var(--border);border-radius:10px;display:grid;place-items:center;background:var(--surface);color:var(--primary);box-shadow:0 12px 28px rgba(0,0,0,.13);font-size:1.2rem;cursor:pointer;transition:.2s ease}.float-btn:hover{background:var(--primary);color:#fff;transform:translateY(-2px)}.float-btn.chat{background:var(--primary);border-color:var(--primary);color:#fff}.float-btn.chat:hover{background:var(--primary-2);border-color:var(--primary-2)}
.panel{position:fixed;right:18px;bottom:86px;width:min(390px,calc(100vw - 32px));max-height:72vh;background:var(--surface);border:1px solid var(--border);border-radius:10px;box-shadow:0 24px 60px rgba(0,0,0,.2);z-index:70;display:none;overflow:hidden}.panel.open{display:flex;flex-direction:column}.panel-head{display:flex;align-items:center;gap:10px;padding:15px 16px;border-bottom:1px solid var(--border);background:#fbfaff}.panel-head strong{flex:1}.panel-body{padding:14px;overflow:auto}.access-row{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)}.switch{display:flex;gap:8px}.chat-messages{display:flex;flex-direction:column;gap:10px;min-height:190px;max-height:360px;overflow:auto}.msg{max-width:88%;padding:10px 12px;border-radius:10px;background:#f2f4f7}.msg.user{align-self:flex-end;background:var(--primary);color:#fff}.chat-quick{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}.chat-quick button{border:1px solid var(--border);background:var(--surface);border-radius:999px;padding:7px 10px;font-weight:700;cursor:pointer;color:var(--primary)}.chat-quick button:hover{background:var(--primary);border-color:var(--primary);color:#fff}.chat-form{display:flex;gap:8px;border-top:1px solid var(--border);padding:12px}.chat-form input{flex:1;border:1px solid #d0d5dd;border-radius:10px;padding:11px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:900px){.nav{align-items:center;flex-wrap:wrap;gap:10px}.mobile-menu-toggle{display:inline-flex}.nav-links,.auth-actions{display:none;width:100%}.nav.mobile-open .nav-links{display:flex;order:3;flex-direction:column;align-items:stretch;padding:8px 0;border-top:1px solid var(--border)}.nav.mobile-open .nav-links a{width:100%;padding:11px 12px}.nav.mobile-open .auth-actions{display:flex;order:4;flex-wrap:wrap;padding:0 0 8px}.nav.mobile-open .auth-actions .btn,.nav.mobile-open .auth-actions form{flex:1;min-width:130px}.nav.mobile-open .auth-actions form .btn{width:100%}.grid{grid-template-columns:1fr 1fr}.brand-name{max-width:180px}}
@media(max-width:640px){.grid{grid-template-columns:1fr}.auth-actions .muted{display:none}.container{padding:24px 16px}.hero{padding-top:26px}.floating-tools{right:12px;bottom:12px}.panel{right:12px;bottom:76px}.nav{padding-inline:14px}.brand-mark{width:40px;height:40px;min-width:40px}.brand-name{max-width:135px;font-size:.88rem}.mobile-menu-toggle{width:42px;height:42px;min-width:42px}.filters{padding:10px}.btn{min-height:46px}}
</style>
@include('partials.platform-layout-styles')
@include('partials.form-control-styles')
<style>
body{display:flex;flex-direction:column;min-height:100dvh}
body>.container{width:100%;max-width:none;flex:1}
.site-header>.nav{width:100%;max-width:none}
@media(min-width:901px) and (max-width:1200px){.nav{flex-wrap:wrap}.nav-links{order:3;width:100%;justify-content:center}.auth-actions{margin-left:auto}}
@media(max-width:640px){.platform-footer-bottom-inner{padding-bottom:148px}}
</style>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<header class="site-header">
<nav class="nav" id="mainNav" aria-label="Main navigation">
<a class="brand" href="{{ route('home') }}">
@if(!empty($platformBrand['logo_url']))
<span class="brand-mark"><img class="brand-logo" src="{{ $platformBrand['logo_url'] }}" alt="{{ $platformBrand['short_name'] }} logo"></span>
@else
<span class="brand-mark brand-fallback"><i class="fas fa-church" aria-hidden="true"></i></span>
@endif
<span class="brand-name">{{ $platformBrand['short_name'] ?? 'COU Youth Platform' }}</span>
</a>
<button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Open navigation menu" aria-controls="mainNav" aria-expanded="false"><i class="fas fa-bars" aria-hidden="true"></i></button>
<div class="nav-links">
<a href="{{ route('public.news') }}">News</a><a href="{{ route('public.events') }}">Events</a><a href="{{ route('public.courses') }}">Courses</a><a href="{{ route('public.life-groups') }}">Life Groups</a><a href="{{ route('public.churches') }}">Church Locator</a><a href="{{ route('public.donate') }}">Donate</a><a href="{{ route('public.about') }}">About</a>
</div>
<div class="auth-actions">@auth<a class="btn" href="{{ route('youth.dashboard') }}"><i class="fas fa-user"></i> {{ auth()->user()->name }}</a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Logout</button></form>@else<a class="btn" href="{{ route('login') }}">Youth Login</a><a class="btn btn-primary" href="{{ route('register') }}">Sign Up</a>@endauth</div>
</nav>
</header>
<main id="main-content" class="container" tabindex="-1">@if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif @yield('content')</main>
<x-platform-footer :brand="$platformBrand" />
<div class="floating-tools" aria-label="Accessibility and support tools"><button class="float-btn" type="button" id="accessToggle" aria-label="Open accessibility settings" title="Accessibility"><i class="fas fa-universal-access" aria-hidden="true"></i></button><button class="float-btn chat" type="button" id="chatToggle" aria-label="Open Youth Assistant" title="Youth Assistant"><i class="fas fa-comments" aria-hidden="true"></i></button></div>
@include('partials.accessibility-panel')
<section class="panel" id="chatPanel" aria-label="Youth Assistant" aria-hidden="true"><div class="panel-head"><i class="fas fa-robot" aria-hidden="true"></i><strong>Youth Assistant</strong><button class="btn btn-icon" type="button" data-close="chatPanel" aria-label="Close Youth Assistant"><i class="fas fa-xmark"></i></button></div><div class="panel-body"><div class="chat-messages" id="chatMessages" aria-live="polite"><div class="msg">Hello! I can help you find events, courses, churches, donations and platform support.</div></div><div class="chat-quick" aria-label="Quick questions"><button type="button" data-chat="Find an event">Find an event</button><button type="button" data-chat="Find a church">Find a church</button><button type="button" data-chat="Show available courses">Courses</button><button type="button" data-chat="How do I donate?">Donate</button><button type="button" data-chat="I need prayer or pastoral support">Prayer support</button></div>@guest<div class="msg">Sign in to chat securely with the Youth Assistant.</div><a class="btn btn-primary" href="{{ route('login') }}" style="width:100%;margin-top:10px">Youth Login</a>@endguest</div>@auth<form class="chat-form" id="chatForm"><label class="sr-only" for="chatInput">Message Youth Assistant</label><input id="chatInput" maxlength="3000" autocomplete="off" placeholder="Ask the Youth Assistant…"><button class="btn btn-primary btn-icon" type="submit" aria-label="Send message"><i class="fas fa-paper-plane"></i></button></form>@endauth</section>
<script>
(() => {
    const nav = document.getElementById('mainNav');
    const toggle = document.getElementById('mobileMenuToggle');
    const setMenu = open => {
        nav.classList.toggle('mobile-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        const icon = toggle.querySelector('i');
        icon.classList.toggle('fa-bars', !open);
        icon.classList.toggle('fa-xmark', open);
    };
    toggle.addEventListener('click', () => setMenu(!nav.classList.contains('mobile-open')));
    nav.querySelectorAll('.nav-links a').forEach(link => link.addEventListener('click', () => setMenu(false)));
    window.addEventListener('resize', () => { if (window.innerWidth > 900) setMenu(false); });
    const form = document.getElementById('chatForm');
    if (!form) return;
    const messages = document.getElementById('chatMessages');
    const input = document.getElementById('chatInput');
    const add = (text, user = false) => {
        const div = document.createElement('div');
        div.className = 'msg' + (user ? ' user' : '');
        div.textContent = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    };
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || input.disabled) return;
        add(message, true); input.value = ''; input.disabled = true;
        try {
            const response = await fetch('{{ route('youth-assistant.reply') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({message})
            });
            const data = await response.json();
            add(data?.data?.reply || data?.message || 'The Youth Assistant is unavailable right now.');
        } catch (_) { add('The Youth Assistant is unavailable right now. Please try again later.'); }
        finally { input.disabled = false; input.focus(); }
    });
})();
</script>
@include('partials.accessibility-enhancements')
</body></html>
