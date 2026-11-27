@extends('youth.layout')
@section('title', 'Life Groups | COU Youth')
@section('youth_content')
@php($joinedCount = count($membershipIds))
<section class="hero">
    <h1>Find your <span class="scribble">people</span></h1>
    <p>
        @if($joinedCount)
            You're part of {{ $joinedCount }} {{ \Illuminate\Support\Str::plural('group', $joinedCount) }}. Life groups are small circles where you can ask real questions, pray for each other and just hang out.
        @else
            Life groups are small circles where you can ask real questions, pray for each other and just hang out. Pick one and go along. Everyone was new once.
        @endif
    </p>
</section>

@if($errors->has('life_group'))<div class="form-errors" role="alert">{{ $errors->first('life_group') }}</div>@endif

<div class="group-grid">
    @forelse($groups as $group)
        @php($joined = in_array($group->id, $membershipIds, true))
        <article class="card group-card {{ $joined ? 'is-joined' : '' }}">
            @if($joined)<span class="joined-flag"><i class="fas fa-check"></i> You're in</span>@endif
            <span class="group-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($group->name, 0, 1)) }}</span>
            <h2>{{ $group->name }}</h2>
            <p class="group-church">{{ $group->organisationUnit?->name ?? 'Church of Uganda Youth' }}</p>
            @if($group->description)<p class="group-desc">{{ \Illuminate\Support\Str::limit($group->description, 160) }}</p>@endif
            <ul class="group-meta">
                @if($group->meeting_day || $group->meeting_time)<li><i class="fas fa-clock"></i> {{ trim(($group->meeting_day ?? '').' '.($group->meeting_time ?? '')) }}</li>@endif
                @if($group->meeting_location)<li><i class="fas fa-location-dot"></i> {{ $group->meeting_location }}</li>@endif
                <li><i class="fas fa-user-group"></i> {{ $group->members_count }} {{ $group->members_count === 1 ? 'person' : 'people' }} so far</li>
            </ul>
            @if($joined)
                <form method="POST" action="{{ route('youth.life-groups.leave', $group) }}">@csrf @method('DELETE')<button class="btn btn-ghost" type="submit">Leave group</button></form>
            @else
                <form method="POST" action="{{ route('youth.life-groups.join', $group) }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-user-plus"></i> Join</button></form>
            @endif
        </article>
    @empty
        <div class="card empty-card">
            <span class="empty-emoji" aria-hidden="true"><i class="fas fa-handshake"></i></span>
            <h3>No groups listed yet</h3>
            <p class="muted">Your church may already have one. Ask your youth leader after Sunday service, or check back soon.</p>
            <a class="btn" href="{{ route('public.churches') }}">Find a church near you</a>
        </div>
    @endforelse
</div>
<div class="pagination">{{ $groups->links() }}</div>

<style>
.group-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.group-card{position:relative;display:flex;flex-direction:column;gap:4px;padding:16px}
.group-card.is-joined{border-color:var(--edge-strong)!important;box-shadow:4px 4px 0 var(--sun)}
.joined-flag{position:absolute;top:16px;right:16px;font-size:.78rem;font-weight:800;color:var(--leaf);background:var(--leaf-soft);padding:4px 10px;border-radius:999px}
.group-avatar{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;font-family:var(--font-display);font-size:1.25rem;font-weight:800;background:var(--sun);color:var(--ink);border:var(--bw) solid var(--edge);margin-bottom:8px;transform:rotate(calc(-5deg * var(--tilt)))}
.group-card:nth-child(3n+2) .group-avatar{background:var(--coral-soft);transform:rotate(calc(4deg * var(--tilt)))}
.group-card:nth-child(3n+3) .group-avatar{background:var(--leaf-soft);transform:rotate(calc(-2deg * var(--tilt)))}
.group-card h2{font-size:1.08rem;margin:0}
.group-church{margin:0;color:var(--primary);font-weight:700;font-size:.9rem}
.group-desc{margin:6px 0 0;color:var(--ink-soft)}
.group-meta{list-style:none;margin:8px 0 12px;padding:0;display:grid;gap:6px;font-size:.88rem;color:var(--ink-soft)}
.group-meta i{width:18px;color:var(--muted)}
.group-card form{margin-top:auto}
.group-card .btn{width:100%}
.form-errors{margin-bottom:18px;padding:13px 16px;border-radius:14px;background:var(--coral-soft);border:1.5px solid var(--coral);color:var(--ink);font-weight:600}
.empty-card{grid-column:1/-1}
@media(max-width:1000px){.group-grid{grid-template-columns:1fr 1fr}}
@media(max-width:620px){.group-grid{grid-template-columns:1fr}}
</style>
@endsection
