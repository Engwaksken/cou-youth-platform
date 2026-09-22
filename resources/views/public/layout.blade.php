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
:root{--primary:{{ $platformBrand['primary_color'] ?? '#4b2e83' }};--primary-2:{{ $platformBrand['secondary_color'] ?? '#204f78' }};--dark:#24183f;--bg:#f6f7fb;--surface:#fff;--text:#1f2937;--muted:#667085;--border:#e4e7ec;--focus:#ffbf47;--radius:18px;--text-scale:1}
*{box-sizing:border-box}
html{scroll-behavior:smooth;font-size:calc(16px * var(--text-scale))}
body{margin:0;font-family:Inter,Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text);line-height:1.55}
body.high-contrast{--bg:#fff;--surface:#fff;--text:#000;--muted:#222;--border:#000;--primary:#24105c}
body.grayscale{filter:grayscale(1)}body.dyslexia{font-family:Verdana,Tahoma,sans-serif;letter-spacing:.035em;word-spacing:.08em}
body.reduce-motion *,body.reduce-motion *::before,body.reduce-motion *::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}
a{text-decoration:none;color:inherit}
a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid var(--focus);outline-offset:3px}
.skip-link{position:absolute;left:-9999px;top:auto}.skip-link:focus{left:16px;top:12px;z-index:1000;background:#fff;color:#000;padding:10px 14px;border-radius:10px}
.site-header{background:rgba(255,255,255,.97);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:40;backdrop-filter:blur(12px)}
.nav{max-width:1180px;margin:auto;padding:13px 20px;display:flex;align-items:center;gap:18px}
.brand{font-weight:900;color:var(--primary);display:flex;align-items:center;gap:9px;margin-right:auto;min-width:0}
.brand-mark{width:42px;height:42px;min-width:42px;border-radius:12px;display:grid;place-items:center;background:#fff;border:1px solid var(--border);overflow:hidden;color:#fff}.brand-mark.brand-fallback{background:linear-gradient(135deg,var(--primary),var(--primary-2))}.brand-logo{display:block;width:100%;height:100%;object-fit:contain;padding:3px}.brand-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}
.mobile-menu-toggle{display:none;width:44px;height:44px;min-width:44px;border:1px solid var(--border);border-radius:11px;background:#fff;color:var(--primary);font-size:1.2rem;align-items:center;justify-content:center;cursor:pointer}
.nav-links{display:flex;align-items:center;gap:4px;flex-wrap:wrap}.nav-links a{font-size:.9rem;font-weight:800;color:#344054;padding:9px 10px;border-radius:9px}.nav-links a:hover,.nav-links a[aria-current="page"]{background:#f0ebff;color:var(--primary)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:11px;padding:10px 14px;font-weight:800;border:1px solid var(--border);background:var(--surface);cursor:pointer;color:var(--text)}.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-2));border-color:transparent;color:#fff}.btn-icon{width:42px;height:42px;padding:0}
.container{max-width:1180px;margin:auto;padding:28px 20px}.hero{padding:46px 0 24px}.hero h1{font-size:clamp(2rem,5vw,3.4rem);line-height:1.05;margin:0 0 12px;letter-spacing:-.03em}.hero p{color:var(--muted);font-size:1.1rem;max-width:760px}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;box-shadow:0 10px 28px rgba(31,41,55,.05)}.card h3{margin-top:0}.muted{color:var(--muted)}.filters{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}.filters input,.filters select{flex:1;min-width:200px;border:1px solid #d0d5dd;border-radius:11px;padding:12px;background:var(--surface);color:var(--text)}.badge{display:inline-flex;border-radius:999px;padding:4px 9px;background:#ede9fe;color:var(--primary);font-size:.75rem;font-weight:900}.pagination{margin-top:20px}.auth-actions{display:flex;gap:8px;align-items:center}
.footer{margin-top:50px;background:var(--dark);color:#fff}.footer-inner{max-width:1180px;margin:auto;padding:30px 20px;display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap}.footer-brand{display:flex;align-items:center;gap:12px}.footer-logo{width:48px;height:48px;object-fit:contain;background:#fff;border-radius:12px;padding:3px}.flash{padding:12px 14px;border-radius:10px;margin-bottom:16px;background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}
.floating-tools{position:fixed;right:18px;bottom:18px;display:flex;flex-direction:column;gap:10px;z-index:60}.float-btn{width:52px;height:52px;border:0;border-radius:16px;display:grid;place-items:center;background:var(--surface);color:var(--primary);box-shadow:0 12px 28px rgba(0,0,0,.16);font-size:1.2rem;cursor:pointer}.float-btn.chat{background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#fff}.panel{position:fixed;right:18px;bottom:86px;width:min(380px,calc(100vw - 32px));max-height:72vh;background:var(--surface);border:1px solid var(--border);border-radius:20px;box-shadow:0 24px 60px rgba(0,0,0,.2);z-index:70;display:none;overflow:hidden}.panel.open{display:flex;flex-direction:column}.panel-head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border)}.panel-head strong{flex:1}.panel-body{padding:14px;overflow:auto}.access-row{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)}.switch{display:flex;gap:8px}.chat-messages{display:flex;flex-direction:column;gap:10px;min-height:190px;max-height:360px;overflow:auto}.msg{max-width:88%;padding:10px 12px;border-radius:14px;background:#f2f4f7}.msg.user{align-self:flex-end;background:#ede9fe}.chat-quick{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}.chat-quick button{border:1px solid var(--border);background:var(--surface);border-radius:999px;padding:7px 10px;font-weight:700;cursor:pointer}.chat-form{display:flex;gap:8px;border-top:1px solid var(--border);padding:12px}.chat-form input{flex:1;border:1px solid #d0d5dd;border-radius:11px;padding:11px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:900px){
 .nav{align-items:center;flex-wrap:wrap;gap:10px}
 .mobile-menu-toggle{display:inline-flex}
 .nav-links,.auth-actions{display:none;width:100%}
 .nav.mobile-open .nav-links{display:flex;order:3;flex-direction:column;align-items:stretch;padding:8px 0;border-top:1px solid var(--border)}
 .nav.mobile-open .nav-links a{width:100%;padding:11px 12px}
 .nav.mobile-open .auth-actions{display:flex;order:4;flex-wrap:wrap;padding:0 0 8px}
 .nav.mobile-open .auth-actions .btn,.nav.mobile-open .auth-actions form{flex:1;min-width:130px}
 .nav.mobile-open .auth-actions form .btn{width:100%}
 .grid{grid-template-columns:1fr 1fr}.brand-name{max-width:180px}
}
@media(max-width:640px){.grid{grid-template-columns:1fr}.auth-actions .muted{display:none}.container{padding:22px 16px}.floating-tools{right:12px;bottom:12px}.panel{right:12px;bottom:76px}.nav{padding-inline:14px}.brand-mark{width:38px;height:38px;min-width:38px}.brand-name{max-width:130px;font-size:.88rem}.mobile-menu-toggle{width:42px;height:42px;min-width:42px}}
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
<a href="{{ route('public.news') }}">News</a><a href="{{ route('public.events') }}">Events</a><a href="{{ route('public.courses') }}">Courses</a><a href="{{ route('public.churches') }}">Church Locator</a><a href="{{ route('public.donate') }}">Donate</a><a href="{{ route('public.about') }}">About</a>
</div>
<div class="auth-actions">@auth<span class="muted">{{ auth()->user()->name }}</span><form method="post" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Logout</button></form>@else<a class="btn" href="{{ route('login') }}">Youth Login</a><a class="btn btn-primary" href="{{ route('register') }}">Sign Up</a>@endauth</div>
</nav>
</header>
<main id="main-content" class="container" tabindex="-1">@if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif @yield('content')</main>
<footer class="footer"><div class="footer-inner"><div class="footer-brand">@if(!empty($platformBrand['logo_url']))<img class="footer-logo" src="{{ $platformBrand['logo_url'] }}" alt="">@endif<div><strong>{{ $platformBrand['name'] }}</strong><p>{{ $platformBrand['tagline'] }}</p></div></div><div>© {{ date('Y') }} Church of Uganda</div></div></footer>

<div class="floating-tools" aria-label="Accessibility and support tools">
<button class="float-btn" type="button" id="accessToggle" aria-label="Open accessibility settings" title="Accessibility"><i class="fas fa-universal-access" aria-hidden="true"></i></button>
<button class="float-btn chat" type="button" id="chatToggle" aria-label="Open Youth Assistant" title="Youth Assistant"><i class="fas fa-comments" aria-hidden="true"></i></button>
</div>

<section class="panel" id="accessPanel" aria-label="Accessibility settings" aria-hidden="true">
<div class="panel-head"><i class="fas fa-universal-access" aria-hidden="true"></i><strong>Accessibility</strong><button class="btn btn-icon" type="button" data-close="accessPanel" aria-label="Close accessibility settings"><i class="fas fa-xmark"></i></button></div>
<div class="panel-body">
<div class="access-row"><span>Text size</span><div class="switch"><button class="btn btn-icon" id="textDown" aria-label="Decrease text size">A−</button><button class="btn btn-icon" id="textUp" aria-label="Increase text size">A+</button></div></div>
<div class="access-row"><label for="contrast">High contrast</label><input id="contrast" type="checkbox"></div>
<div class="access-row"><label for="grayscale">Grayscale</label><input id="grayscale" type="checkbox"></div>
<div class="access-row"><label for="dyslexia">Dyslexia-friendly reading</label><input id="dyslexia" type="checkbox"></div>
<div class="access-row"><label for="motion">Reduce motion</label><input id="motion" type="checkbox"></div>
<button class="btn" type="button" id="resetAccessibility" style="margin-top:14px;width:100%">Reset accessibility settings</button>
</div>
</section>

<section class="panel" id="chatPanel" aria-label="Youth Assistant" aria-hidden="true">
<div class="panel-head"><i class="fas fa-robot" aria-hidden="true"></i><strong>Youth Assistant</strong><button class="btn btn-icon" type="button" data-close="chatPanel" aria-label="Close Youth Assistant"><i class="fas fa-xmark"></i></button></div>
<div class="panel-body">
<div class="chat-messages" id="chatMessages" aria-live="polite"><div class="msg">Hello! I can help you find events, courses, churches, donations and platform support.</div></div>
<div class="chat-quick" aria-label="Quick questions"><button type="button" data-chat="Find an event">Find an event</button><button type="button" data-chat="Find a church">Find a church</button><button type="button" data-chat="Show available courses">Courses</button><button type="button" data-chat="How do I donate?">Donate</button><button type="button" data-chat="I need prayer or pastoral support">Prayer support</button></div>
@guest<div class="msg">Sign in to chat securely with the Youth Assistant.</div><a class="btn btn-primary" href="{{ route('login') }}" style="width:100%;margin-top:10px">Youth Login</a>@endguest
</div>
@auth<form class="chat-form" id="chatForm"><label class="sr-only" for="chatInput">Message Youth Assistant</label><input id="chatInput" maxlength="3000" autocomplete="off" placeholder="Ask the Youth Assistant…"><button class="btn btn-primary btn-icon" type="submit" aria-label="Send message"><i class="fas fa-paper-plane"></i></button></form>@endauth
</section>

<script>
(()=>{
const body=document.body;const root=document.documentElement;const state=JSON.parse(localStorage.getItem('cou-accessibility')||'{}');
const nav=document.getElementById('mainNav');const menuToggle=document.getElementById('mobileMenuToggle');
const closeMobileMenu=()=>{if(!nav||!menuToggle)return;nav.classList.remove('mobile-open');menuToggle.setAttribute('aria-expanded','false');menuToggle.setAttribute('aria-label','Open navigation menu');const icon=menuToggle.querySelector('i');if(icon){icon.classList.remove('fa-xmark');icon.classList.add('fa-bars')}};
if(nav&&menuToggle){menuToggle.addEventListener('click',()=>{const open=nav.classList.toggle('mobile-open');menuToggle.setAttribute('aria-expanded',open?'true':'false');menuToggle.setAttribute('aria-label',open?'Close navigation menu':'Open navigation menu');const icon=menuToggle.querySelector('i');if(icon){icon.classList.toggle('fa-bars',!open);icon.classList.toggle('fa-xmark',open)}});nav.querySelectorAll('.nav-links a').forEach(link=>link.addEventListener('click',closeMobileMenu));window.addEventListener('resize',()=>{if(window.innerWidth>900)closeMobileMenu()});}
const apply=()=>{root.style.setProperty('--text-scale',state.scale||1);body.classList.toggle('high-contrast',!!state.contrast);body.classList.toggle('grayscale',!!state.grayscale);body.classList.toggle('dyslexia',!!state.dyslexia);body.classList.toggle('reduce-motion',!!state.motion);['contrast','grayscale','dyslexia','motion'].forEach(id=>{const el=document.getElementById(id);if(el)el.checked=!!state[id]});localStorage.setItem('cou-accessibility',JSON.stringify(state))};apply();
const openPanel=id=>{['accessPanel','chatPanel'].forEach(p=>{const el=document.getElementById(p);const open=p===id&&!el.classList.contains('open');el.classList.toggle('open',open);el.setAttribute('aria-hidden',open?'false':'true')})};
document.getElementById('accessToggle').onclick=()=>openPanel('accessPanel');document.getElementById('chatToggle').onclick=()=>openPanel('chatPanel');document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>{const p=document.getElementById(b.dataset.close);p.classList.remove('open');p.setAttribute('aria-hidden','true')});document.getElementById('textUp').onclick=()=>{state.scale=Math.min(1.4,(state.scale||1)+.1);apply()};document.getElementById('textDown').onclick=()=>{state.scale=Math.max(.9,(state.scale||1)-.1);apply()};['contrast','grayscale','dyslexia','motion'].forEach(id=>{document.getElementById(id).onchange=e=>{state[id]=e.target.checked;apply()}});document.getElementById('resetAccessibility').onclick=()=>{Object.keys(state).forEach(k=>delete state[k]);apply()};document.querySelectorAll('[data-chat]').forEach(b=>b.onclick=()=>{openPanel('chatPanel');const input=document.getElementById('chatInput');if(input){input.value=b.dataset.chat;input.focus()}});
const form=document.getElementById('chatForm');if(form){const messages=document.getElementById('chatMessages');const input=document.getElementById('chatInput');const add=(text,user=false)=>{const div=document.createElement('div');div.className='msg'+(user?' user':'');div.textContent=text;messages.appendChild(div);messages.scrollTop=messages.scrollHeight};form.addEventListener('submit',async e=>{e.preventDefault();const message=input.value.trim();if(!message)return;add(message,true);input.value='';input.disabled=true;try{const r=await fetch('{{ route('youth-assistant.reply') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({message})});const data=await r.json();add(data?.data?.reply||data?.message||'The Youth Assistant is unavailable right now.')}catch(_){add('The Youth Assistant is unavailable right now. Please try again later.')}finally{input.disabled=false;input.focus()}})}})();
</script>
</body></html>
