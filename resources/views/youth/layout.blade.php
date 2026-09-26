@extends('public.layout')

@section('content')
<div class="youth-shell">
    <button class="youth-sidebar-toggle" id="youthSidebarToggle" type="button" aria-controls="youthSidebar" aria-expanded="false">
        <i class="fas fa-bars" aria-hidden="true"></i>
        <span>Portal menu</span>
    </button>

    <aside class="youth-sidebar" id="youthSidebar" aria-label="Youth portal navigation">
        <div class="youth-sidebar-profile">
            <span class="youth-avatar"><i class="fas fa-user" aria-hidden="true"></i></span>
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <small>Youth Portal</small>
            </div>
        </div>

        <nav class="youth-sidebar-nav">
            <a href="{{ route('youth.dashboard') }}" class="{{ request()->routeIs('youth.dashboard') ? 'active' : '' }}"><i class="fas fa-gauge-high"></i><span>Dashboard</span></a>
            <a href="{{ route('youth.profile') }}" class="{{ request()->routeIs('youth.profile*') ? 'active' : '' }}"><i class="fas fa-user-pen"></i><span>My Profile</span></a>
            <a href="{{ route('youth.learning') }}" class="{{ request()->routeIs('youth.learning') ? 'active' : '' }}"><i class="fas fa-graduation-cap"></i><span>My Learning</span></a>
            <a href="{{ route('youth.life-groups') }}" class="{{ request()->routeIs('youth.life-groups*') ? 'active' : '' }}"><i class="fas fa-people-group"></i><span>Life Groups</span></a>
            <a href="{{ route('youth.events') }}" class="{{ request()->routeIs('youth.events*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i><span>My Events</span></a>
            <a href="{{ route('youth.certificates') }}" class="{{ request()->routeIs('youth.certificates') ? 'active' : '' }}"><i class="fas fa-certificate"></i><span>Certificates</span></a>
            <a href="{{ route('youth.notifications') }}" class="{{ request()->routeIs('youth.notifications*') ? 'active' : '' }}"><i class="fas fa-bell"></i><span>Notifications</span></a>
            <a href="{{ route('youth.notification-preferences') }}" class="{{ request()->routeIs('youth.notification-preferences*') ? 'active' : '' }}"><i class="fas fa-sliders"></i><span>Preferences</span></a>
            <a href="{{ route('youth.media') }}" class="{{ request()->routeIs('youth.media') ? 'active' : '' }}"><i class="fas fa-photo-film"></i><span>Media & Services</span></a>
            <a href="{{ route('public.prayer') }}" class="{{ request()->routeIs('public.prayer*') ? 'active' : '' }}"><i class="fas fa-hands-praying"></i><span>Prayer Support</span></a>
        </nav>

        <div class="youth-sidebar-footer">
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-arrow-up-right-from-square"></i><span>Public Website</span></a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><i class="fas fa-right-from-bracket"></i><span>Logout</span></button></form>
        </div>
    </aside>

    <div class="youth-sidebar-backdrop" id="youthSidebarBackdrop" hidden></div>

    <section class="youth-main">
        @if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif
        @yield('youth_content')
    </section>
</div>

<style>
.site-header,.footer{display:none!important}
body{background:var(--bg)}
#main-content.container{max-width:none!important;width:100%!important;padding:0!important;margin:0!important}
.youth-shell{display:grid;grid-template-columns:260px minmax(0,1fr);min-height:100vh;width:100%;align-items:stretch}
.youth-sidebar{position:sticky;top:0;height:100vh;min-height:100vh;display:flex;flex-direction:column;background:var(--primary);color:#fff;overflow-y:auto;overflow-x:hidden;box-shadow:10px 0 30px rgba(31,20,56,.16);z-index:20}
.youth-sidebar-profile{display:flex;align-items:center;gap:11px;padding:20px 16px 18px;border-bottom:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.04)}
.youth-avatar{width:44px;height:44px;min-width:44px;border-radius:10px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);color:#fff;display:grid;place-items:center}
.youth-sidebar-profile strong,.youth-sidebar-profile small{display:block;color:#fff}.youth-sidebar-profile strong{font-size:.92rem}.youth-sidebar-profile small{font-size:.75rem;opacity:.78}
.youth-sidebar-nav{padding:12px 10px;flex:1}
.youth-sidebar-nav a,.youth-sidebar-footer a,.youth-sidebar-footer button{display:flex;align-items:center;gap:11px;width:100%;min-height:44px;padding:10px 12px;border:0;border-radius:9px;background:transparent;color:rgba(255,255,255,.9);font:inherit;font-size:.86rem;font-weight:800;text-align:left;cursor:pointer;transition:background-color .18s ease,color .18s ease,transform .18s ease}
.youth-sidebar-nav a i,.youth-sidebar-footer i{width:19px;text-align:center;color:rgba(255,255,255,.95)}
.youth-sidebar-nav a:hover,.youth-sidebar-footer a:hover,.youth-sidebar-footer button:hover{background:rgba(255,255,255,.14);color:#fff;transform:translateX(2px)}
.youth-sidebar-nav a.active{background:rgba(255,255,255,.22);color:#fff;box-shadow:inset 3px 0 0 rgba(255,255,255,.96)}
.youth-sidebar-nav a.active i{color:#fff}
.youth-sidebar-footer{padding:10px;border-top:1px solid rgba(255,255,255,.16);background:rgba(0,0,0,.06)}.youth-sidebar-footer form{margin:0}
.youth-main{min-width:0;padding:30px 32px 42px;max-width:1500px;width:100%;margin:0 auto}
.youth-sidebar-toggle{display:none;align-items:center;gap:9px;width:100%;margin-bottom:14px;padding:11px 13px;border:1px solid var(--border);border-radius:10px;background:var(--primary);color:#fff;font-weight:900;cursor:pointer}
.youth-sidebar-toggle:hover{filter:brightness(1.08)}
.youth-sidebar-backdrop{display:none}
@media(max-width:900px){.youth-shell{display:block;min-height:100vh}.youth-main{padding:18px 16px 34px}.youth-sidebar-toggle{display:flex}.youth-sidebar{position:fixed;left:0;top:0;bottom:0;width:min(290px,86vw);height:100vh;min-height:100vh;z-index:120;transform:translateX(-105%);transition:transform .22s ease}.youth-sidebar.open{transform:translateX(0)}.youth-sidebar-backdrop{position:fixed;inset:0;background:rgba(16,24,40,.48);z-index:110}.youth-sidebar-backdrop.open{display:block}.youth-sidebar-profile{padding-top:22px}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{const toggle=document.getElementById('youthSidebarToggle');const sidebar=document.getElementById('youthSidebar');const backdrop=document.getElementById('youthSidebarBackdrop');if(!toggle||!sidebar||!backdrop)return;const close=()=>{sidebar.classList.remove('open');backdrop.classList.remove('open');backdrop.hidden=true;toggle.setAttribute('aria-expanded','false')};const open=()=>{sidebar.classList.add('open');backdrop.hidden=false;backdrop.classList.add('open');toggle.setAttribute('aria-expanded','true')};toggle.addEventListener('click',()=>sidebar.classList.contains('open')?close():open());backdrop.addEventListener('click',close);sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));window.addEventListener('resize',()=>{if(window.innerWidth>900)close()});});
</script>
@endsection
