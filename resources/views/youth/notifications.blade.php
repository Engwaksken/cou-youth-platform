@extends('youth.layout')
@section('title', 'Updates | COU Youth')
@section('youth_content')
@php($unreadOnPage = collect($receipts->items())->whereNull('read_at')->count())
<section class="hero hero-row">
    <div>
        <h1><span class="scribble">Updates</span></h1>
        <p>News from your church and the youth team, all in one place.</p>
    </div>
    <div class="hero-actions">
        <a class="btn btn-ghost" href="{{ route('youth.notification-preferences') }}"><i class="fas fa-sliders"></i> Settings</a>
        @if($unreadOnPage)
            <form method="POST" action="{{ route('youth.notifications.read-all') }}">@csrf @method('PATCH')<button class="btn" type="submit"><i class="fas fa-check-double"></i> Mark all as read</button></form>
        @endif
    </div>
</section>

<div class="card notification-list">
    @forelse($receipts as $receipt)
        @if($receipt->notification)
            <article class="note {{ $receipt->read_at ? '' : 'unread' }}">
                <span class="note-dot" aria-hidden="true"></span>
                <div class="note-copy">
                    <div class="note-head">
                        <h2>{{ $receipt->notification->title }}</h2>
                        @if(!$receipt->read_at)<span class="badge is-new">New</span>@endif
                    </div>
                    <p>{{ $receipt->notification->body }}</p>
                    <small>{{ optional($receipt->notification->created_at)->diffForHumans() }}</small>
                </div>
                <form method="POST" action="{{ route('youth.notifications.read', $receipt) }}">@csrf @method('PATCH')
                    @if($receipt->notification->action_url)
                        <button class="btn btn-primary" type="submit">Open <i class="fas fa-arrow-right"></i></button>
                    @elseif(!$receipt->read_at)
                        <button class="btn btn-ghost" type="submit">Got it</button>
                    @endif
                </form>
            </article>
        @endif
    @empty
        <div class="empty-note"><span class="empty-emoji" aria-hidden="true">📭</span><h3>Nothing here yet</h3><p class="muted">When your church or the youth team shares news, it'll land here.</p></div>
    @endforelse
</div>
<div class="pagination">{{ $receipts->links() }}</div>

<style>
.hero-row{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
.hero-actions{display:flex;gap:8px;flex-wrap:wrap}
.hero-actions form{margin:0}
.notification-list{padding:6px;max-width:52rem}
.note{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:14px;align-items:start;padding:16px}
.note+.note{border-top:1px solid var(--line)}
.note-dot{width:10px;height:10px;margin-top:8px;border-radius:50%;background:transparent}
.note.unread .note-dot{background:var(--coral)}
.note.unread{background:linear-gradient(90deg,var(--sun-soft),transparent 70%);border-radius:14px}
.note-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.note-head h2{font-size:1.08rem;margin:0}
.note:not(.unread) .note-head h2{font-weight:650;color:var(--ink-soft)}
.note-copy p{margin:4px 0;color:var(--ink-soft)}
.note-copy small{color:var(--muted)}
.note form{margin:0}
.empty-note{border:0!important}
@media(max-width:640px){.note{grid-template-columns:auto minmax(0,1fr)}.note form{grid-column:2}.note form .btn{width:100%}}
</style>
@endsection
