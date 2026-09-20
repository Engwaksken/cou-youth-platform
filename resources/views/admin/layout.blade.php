<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COU Youth CMS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root{--primary:#4b2e83;--primary-dark:#35205f;--bg:#f5f7fb;--surface:#fff;--border:#e5e7eb;--text:#1f2937;--muted:#6b7280;--success:#15803d;--warning:#b45309;--danger:#b91c1c}
        *{box-sizing:border-box} body{font-family:Arial,Helvetica,sans-serif;margin:0;background:var(--bg);color:var(--text)} a{text-decoration:none;color:inherit} button,input,select,textarea{font:inherit}
        .top{height:68px;background:#fff;padding:0 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}.brand{display:flex;align-items:center;gap:10px;font-weight:800;color:var(--primary)}.brand i{font-size:22px}.top-actions{display:flex;align-items:center;gap:10px}
        .wrap{display:grid;grid-template-columns:260px minmax(0,1fr);min-height:calc(100vh - 68px)}.side{background:#fff;border-right:1px solid var(--border);padding:18px 14px;position:sticky;top:68px;height:calc(100vh - 68px);overflow:auto}.nav-label{font-size:11px;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;padding:15px 12px 6px}.side a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:9px;color:#374151;font-weight:600;font-size:14px}.side a i{width:19px;text-align:center;color:var(--primary)}.side a:hover,.side a.active{background:#f1edfa;color:var(--primary)}
        .main{padding:24px;min-width:0}.page-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}.page-head h1{margin:0;font-size:27px}.page-head h1 i{color:var(--primary);margin-right:8px}.page-head p{margin:7px 0 0;color:var(--muted)}
        .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:17px;box-shadow:0 4px 18px rgba(15,23,42,.035)}
        .alert{padding:13px 15px;border-radius:9px;margin-bottom:16px;background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}.alert-error{background:#fef2f2;border-color:#fecaca;color:#991b1b}.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid transparent;border-radius:9px;padding:9px 14px;font-weight:700;cursor:pointer;background:#fff}.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}.btn-light{border-color:var(--border);color:#374151}.btn-danger{background:#fee2e2;color:var(--danger)}.icon-btn{border:1px solid var(--border);background:#fff;width:36px;height:36px;border-radius:9px;cursor:pointer;color:#4b5563}.icon-btn:hover{background:#f9fafb;color:var(--primary)}
        .stats-grid{margin-bottom:16px}.stat-card{display:flex;align-items:center;gap:14px}.stat-card .stat-icon{width:44px;height:44px;border-radius:11px;display:grid;place-items:center;background:#ede9fe;color:var(--primary);font-size:19px}.stat-card strong{display:block;font-size:22px}.stat-card span{display:block;color:var(--muted);font-size:13px;margin-top:3px}
        .tabs{display:flex;gap:4px;margin:14px 0;border-bottom:1px solid var(--border);overflow-x:auto;scrollbar-width:thin}.tab{white-space:nowrap;border:0;background:transparent;padding:10px 13px;cursor:pointer;font-weight:700;color:var(--muted);border-bottom:3px solid transparent}.tab.active{color:var(--primary);border-color:var(--primary)}.tab-panel{display:none}.tab-panel.active{display:block}.tab-count{display:inline-flex;min-width:20px;height:20px;padding:0 6px;border-radius:999px;align-items:center;justify-content:center;background:#f3f4f6;color:#4b5563;font-size:11px;margin-left:5px}
        .filters{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px}.filters input,.filters select,.form-grid input,.form-grid select,.form-grid textarea{width:100%;border:1px solid #d1d5db;border-radius:9px;padding:10px;background:#fff}.toolbar{display:flex;gap:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:12px}.toolbar .filters{flex:1}.actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
        .table-card{margin-top:12px;padding:0;overflow:hidden}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:850px}th,td{text-align:left;padding:12px 14px;border-bottom:1px solid #eef0f3;vertical-align:top}th{background:#fafafa;color:#4b5563;font-size:12px;text-transform:uppercase;letter-spacing:.04em}td small{display:block;color:var(--muted);margin-top:4px}.badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px;font-weight:800}.badge-success{background:#dcfce7;color:#166534}.badge-warning{background:#fef3c7;color:#92400e}.badge-danger{background:#fee2e2;color:#991b1b}.pagination{padding:12px 14px}.empty{text-align:center;color:var(--muted);padding:30px}
        .campaign-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.campaign-card h3{margin:0 0 7px}.campaign-card p{color:var(--muted);line-height:1.55;min-height:48px}.campaign-top{display:flex;justify-content:space-between;gap:12px}.progress{height:8px;background:#ede9fe;border-radius:99px;overflow:hidden;margin:17px 0 8px}.progress span{display:block;height:100%;background:var(--primary)}.campaign-numbers{display:flex;justify-content:space-between;gap:8px;align-items:center}.campaign-numbers span,.campaign-meta{font-size:12px;color:var(--muted)}.campaign-meta{display:flex;gap:12px;flex-wrap:wrap;margin:13px 0}
        .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(15,23,42,.58);padding:20px;align-items:center;justify-content:center}.modal.open{display:flex}.modal-card{width:min(760px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:15px;padding:20px;box-shadow:0 24px 80px rgba(15,23,42,.3)}.modal-card.small{width:min(460px,100%)}.modal-card.large{width:min(980px,100%)}.modal-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;position:sticky;top:-20px;background:#fff;padding:3px 0 10px;z-index:2}.modal-head h2{margin:0;font-size:20px}.modal-body{color:#374151}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.detail-item{padding:11px;border:1px solid var(--border);border-radius:10px;background:#fafafa}.detail-item span{display:block;font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:800;margin-bottom:4px}.detail-item strong{word-break:break-word}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.form-grid label{font-size:13px;font-weight:700;color:#4b5563}.form-grid label input,.form-grid label select,.form-grid label textarea{margin-top:6px}.span-2{grid-column:span 2}.checkbox{display:flex;align-items:center;gap:8px}.checkbox input{width:auto;margin:0}.modal-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}.danger-copy{color:var(--danger);background:#fef2f2;border:1px solid #fecaca;padding:12px;border-radius:10px}.logout-form{margin:0}.mobile-menu{display:none}.errors{margin:0;padding-left:18px}
        @media(max-width:1100px){.grid,.campaign-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filters{grid-template-columns:1fr 1fr}}
        @media(max-width:800px){.wrap{grid-template-columns:1fr}.side{display:none}.main{padding:16px}.grid,.campaign-grid{grid-template-columns:1fr}.page-head{align-items:flex-start;flex-direction:column}.filters,.form-grid,.detail-grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.top{padding:0 14px}.top .user-name{display:none}.modal{padding:10px}.modal-card{max-height:94vh;padding:16px}.toolbar{align-items:stretch}.toolbar .filters{width:100%}}
    </style>
    @stack('styles')
</head>
<body>
<div class="top">
    <a class="brand" href="{{ route('admin.dashboard') }}"><i class="fas fa-church"></i><span>COU Youth Platform CMS</span></a>
    <div class="top-actions"><a class="btn btn-light" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> Website</a><span class="user-name">{{ auth()->user()->name ?? 'Administrator' }}</span><form class="logout-form" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-light" type="submit"><i class="fas fa-right-from-bracket"></i> Logout</button></form></div>
</div>
<div class="wrap">
    <aside class="side">
        <div class="nav-label">Overview</div><a href="{{ route('admin.dashboard') }}"><i class="fas fa-gauge-high"></i> Dashboard</a>
        <div class="nav-label">Ministry</div><a href="{{ route('admin.organisation-units.index') }}"><i class="fas fa-sitemap"></i> Church Structure</a><a href="{{ route('admin.content.index') }}"><i class="fas fa-newspaper"></i> Content</a><a href="{{ route('admin.events.index') }}"><i class="fas fa-calendar-days"></i> Events</a><a href="{{ route('admin.life-groups.index') }}"><i class="fas fa-people-group"></i> Life Groups</a><a href="{{ route('admin.courses.index') }}"><i class="fas fa-book-bible"></i> Courses</a><a href="{{ route('admin.media.index') }}"><i class="fas fa-photo-film"></i> Media</a><a href="{{ route('admin.church-locations.index') }}"><i class="fas fa-location-dot"></i> Church Locator</a><a href="{{ route('admin.prayer.index') }}"><i class="fas fa-hands-praying"></i> Prayer & Pastoral</a>
        <div class="nav-label">Finance</div><a href="{{ route('admin.donations.index') }}"><i class="fas fa-hand-holding-heart"></i> Donations</a><a href="{{ route('admin.payment-gateways.index') }}"><i class="fas fa-credit-card"></i> Payment Gateways</a>
        <div class="nav-label">AI & Communication</div><a href="{{ route('admin.ai.index') }}"><i class="fas fa-robot"></i> AI Settings</a><a href="{{ route('admin.notifications.index') }}"><i class="fas fa-bell"></i> Notifications</a>
        <div class="nav-label">Safety</div><a href="{{ route('admin.guardian-consents.index') }}"><i class="fas fa-shield-heart"></i> Safeguarding</a><a href="{{ route('admin.moderation.index') }}"><i class="fas fa-shield-halved"></i> Moderation</a>
        <div class="nav-label">Insights</div><a href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-column"></i> Reports</a><a href="{{ route('admin.system.health') }}"><i class="fas fa-heart-pulse"></i> System Health</a>
    </aside>
    <main class="main">
        @if(session('success'))<div class="alert" data-auto-dismiss><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-error" data-auto-dismiss><strong><i class="fas fa-triangle-exclamation"></i> Please correct the following:</strong><ul class="errors">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<script>
(function(){
    const openModal=(id)=>{const el=document.getElementById(id);if(el){el.classList.add('open');document.body.style.overflow='hidden';}};
    const closeModal=(el)=>{const modal=el?.classList?.contains('modal')?el:el?.closest?.('.modal');if(modal){modal.classList.remove('open');if(!document.querySelector('.modal.open'))document.body.style.overflow='';}};
    window.openModal=openModal;window.closeModal=closeModal;
    document.addEventListener('click',(e)=>{
        const opener=e.target.closest('[data-open-modal]');if(opener){e.preventDefault();openModal(opener.dataset.openModal);return;}
        const closer=e.target.closest('[data-close-modal]');if(closer){e.preventDefault();closeModal(closer);return;}
        if(e.target.classList.contains('modal'))closeModal(e.target);
        const tab=e.target.closest('[data-tab-target]');if(tab){
            const group=tab.closest('[data-tabs]')||document;
            group.querySelectorAll('[data-tab-target]').forEach(x=>x.classList.remove('active'));
            group.querySelectorAll('.tab-panel').forEach(x=>x.classList.remove('active'));
            tab.classList.add('active');const panel=document.getElementById(tab.dataset.tabTarget);if(panel)panel.classList.add('active');
            if(history.replaceState)history.replaceState(null,'','#'+tab.dataset.tabTarget);
        }
    });
    document.addEventListener('keydown',(e)=>{if(e.key==='Escape'){const modal=document.querySelector('.modal.open');if(modal)closeModal(modal);}});
    document.querySelectorAll('[data-tabs]').forEach(group=>{
        const hash=location.hash.replace('#','');if(hash){const tab=group.querySelector(`[data-tab-target="${CSS.escape(hash)}"]`);if(tab)tab.click();}
    });
    setTimeout(()=>document.querySelectorAll('[data-auto-dismiss]').forEach((el)=>{el.style.transition='opacity .35s';el.style.opacity='0';setTimeout(()=>el.remove(),350);}),5000);
})();
</script>
@stack('scripts')
</body>
</html>
