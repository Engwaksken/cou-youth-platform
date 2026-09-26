@extends('youth.layout')
@section('title', 'Notifications | COU Youth')
@section('youth_content')
@php
    $pageReceipts = collect($receipts->items());
    $unreadOnPage = $pageReceipts->whereNull('read_at')->count();
    $readOnPage = $pageReceipts->filter(fn($item) => !empty($item->read_at))->count();
@endphp
<section class="hero"><span class="section-kicker">UPDATES</span><h1>Notifications</h1><p>See platform updates, learning alerts, events and messages relevant to your youth journey.</p></section>
<div class="page-stats">
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-bell"></i></span><div><strong>{{ $receipts->total() }}</strong><span>Total notifications</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-envelope"></i></span><div><strong>{{ $unreadOnPage }}</strong><span>Unread on this page</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-envelope-open"></i></span><div><strong>{{ $readOnPage }}</strong><span>Read on this page</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-list"></i></span><div><strong>{{ $pageReceipts->count() }}</strong><span>Shown on this page</span></div></div>
</div>
<div class="toolbar"><a class="btn" href="{{ route('youth.dashboard') }}"><i class="fas fa-arrow-left"></i> Dashboard</a><a class="btn" href="{{ route('youth.notification-preferences') }}"><i class="fas fa-sliders"></i> Preferences</a><form method="POST" action="{{ route('youth.notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-primary" type="submit"><i class="fas fa-check-double"></i> Mark all read</button></form></div>
<div class="notification-list">
@forelse($receipts as $receipt)
@if($receipt->notification)
<article class="card notification-card {{ $receipt->read_at ? '' : 'notification-unread' }}"><div class="notification-icon"><i class="fas fa-bell"></i></div><div class="notification-copy"><div class="notification-heading"><h2>{{ $receipt->notification->title }}</h2>@if(!$receipt->read_at)<span class="badge">New</span>@endif</div><p>{{ $receipt->notification->body }}</p><small>{{ optional($receipt->notification->created_at)->diffForHumans() }}</small></div><form method="POST" action="{{ route('youth.notifications.read',$receipt) }}">@csrf @method('PATCH')<button class="btn" type="submit">{{ $receipt->notification->action_url ? 'Open' : 'Mark read' }}</button></form></article>
@endif
@empty
<div class="card empty-state"><i class="fas fa-bell-slash"></i><h3>No notifications yet</h3><p class="muted">Platform updates for your account will appear here.</p></div>
@endforelse
</div>
<div class="pagination">{{ $receipts->links() }}</div>
<style>.toolbar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px;flex-wrap:wrap}.notification-list{display:flex;flex-direction:column;gap:12px}.notification-card{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:14px}.notification-unread{border-left:4px solid var(--primary);background:#fdfbff}.notification-icon{width:44px;height:44px;border-radius:10px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}.notification-heading{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.notification-heading h2{font-size:1rem;margin:0}.notification-copy p{margin:5px 0;color:#475467}.notification-copy small{color:var(--muted)}.empty-state{text-align:center;padding:34px}.empty-state>i{font-size:2rem;color:var(--primary);margin-bottom:10px}@media(max-width:700px){.notification-card{grid-template-columns:auto 1fr}.notification-card form{grid-column:1/-1}.notification-card .btn{width:100%}}</style>
@endsection
