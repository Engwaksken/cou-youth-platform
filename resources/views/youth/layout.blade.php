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
            <a href="{{ route('public.prayer') }}"><i class="fas fa-hands-praying"></i><span>Prayer Support</span></a>
        </nav>

        <div class="youth-sidebar-footer">
            <a href="{{ route('home') }}"><i class="fas fa-house"></i><span>Public website</span></a>
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
.youth-shell{display:grid;grid-template-columns:245px minmax(0,1fr);gap:24px;align-items:start}.youth-sidebar{position:sticky;top:92px;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow-sm);overflow:hidden}.youth-sidebar-profile{display:flex;align-items:center;gap:11px;padding:16px;border-bottom:1px solid var(--border);background:#fbfaff}.youth-avatar{width:42px;height:42px;min-width:42px;border-radius:10px;background:var(--primary);color:#fff;display:grid;place-items:center}.youth-sidebar-profile strong,.youth-sidebar-profile small{display:block}.youth-sidebar-profile strong{font-size:.9rem}.youth-sidebar-profile small{font-size:.75rem;color:var(--muted)}.youth-sidebar-nav{padding:10px}.youth-sidebar-nav a,.youth-sidebar-footer a,.youth-sidebar-footer button{display:flex;align-items:center;gap:11px;width:100%;min-height:42px;padding:9px 11px;border:0;border-radius:9px;background:transparent;color:#475467;font:inherit;font-size:.86rem;font-weight:800;text-align:left;cursor:pointer}.youth-sidebar-nav a i,.youth-sidebar-footer i{width:18px;text-align:center;color:var(--primary)}.youth-sidebar-nav a:hover,.youth-sidebar-nav a.active,.youth-sidebar-footer a:hover,.youth-sidebar-footer button:hover{background:#f0ebf8;color:var(--primary)}.youth-sidebar-nav a.active{box-shadow:inset 3px 0 0 var(--primary)}.youth-sidebar-footer{padding:10px;border-top:1px solid var(--border)}.youth-sidebar-footer form{margin:0}.youth-main{min-width:0}.youth-sidebar-toggle{display:none;align-items:center;gap:9px;width:100%;margin-bottom:14px;padding:11px 13px;border:1px solid var(--border);border-radius:10px;background:#fff;color:var(--primary);font-weight:900;cursor:pointer}.youth-sidebar-backdrop{display:none}
@media(max-width:900px){.youth-shell{display:block}.youth-sidebar-toggle{display:flex}.youth-sidebar{position:fixed;left:0;top:0;bottom:0;width:min(290px,86vw);border-radius:0;z-index:120;transform:translateX(-105%);transition:transform .22s ease;overflow-y:auto}.youth-sidebar.open{transform:translateX(0)}.youth-sidebar-backdrop{position:fixed;inset:0;background:rgba(16,24,40,.48);z-index:110}.youth-sidebar-backdrop.open{display:block}.youth-sidebar-profile{padding-top:22px}.youth-main{width:100%}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{const toggle=document.getElementById('youthSidebarToggle');const sidebar=document.getElementById('youthSidebar');const backdrop=document.getElementById('youthSidebarBackdrop');if(!toggle||!sidebar||!backdrop)return;const close=()=>{sidebar.classList.remove('open');backdrop.classList.remove('open');backdrop.hidden=true;toggle.setAttribute('aria-expanded','false')};const open=()=>{sidebar.classList.add('open');backdrop.hidden=false;backdrop.classList.add('open');toggle.setAttribute('aria-expanded','true')};toggle.addEventListener('click',()=>sidebar.classList.contains('open')?close():open());backdrop.addEventListener('click',close);sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));window.addEventListener('resize',()=>{if(window.innerWidth>900)close()});});
</script>
@endsection
