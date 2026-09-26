@extends('youth.layout')
@section('title', 'My Life Groups | COU Youth')
@section('youth_content')
<section class="hero"><span class="section-kicker">COMMUNITY</span><h1>Life Groups</h1><p>Join an active youth fellowship, connect with others and grow through Scripture, prayer and service.</p></section>
@if($errors->has('life_group'))<div class="form-errors" role="alert">{{ $errors->first('life_group') }}</div>@endif
<div class="group-grid">
@forelse($groups as $group)
<article class="card group-card">
<div class="group-top"><span class="group-icon"><i class="fas fa-people-group"></i></span><div><h2>{{ $group->name }}</h2><p>{{ $group->organisationUnit?->name ?? 'Church of Uganda Youth' }}</p></div></div>
@if($group->description)<p class="muted">{{ \Illuminate\Support\Str::limit($group->description, 180) }}</p>@endif
<div class="group-meta">
@if($group->meeting_day)<span><i class="fas fa-calendar-day"></i> {{ $group->meeting_day }}</span>@endif
@if($group->meeting_time)<span><i class="fas fa-clock"></i> {{ $group->meeting_time }}</span>@endif
@if($group->meeting_location)<span><i class="fas fa-location-dot"></i> {{ $group->meeting_location }}</span>@endif
<span><i class="fas fa-user-group"></i> {{ $group->members_count }} member{{ $group->members_count === 1 ? '' : 's' }}</span>
</div>
@if(in_array($group->id,$membershipIds,true))
<form method="POST" action="{{ route('youth.life-groups.leave',$group) }}">@csrf @method('DELETE')<button class="btn" type="submit"><i class="fas fa-arrow-right-from-bracket"></i> Leave group</button></form>
@else
<form method="POST" action="{{ route('youth.life-groups.join',$group) }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-user-plus"></i> Join group</button></form>
@endif
</article>
@empty
<div class="card"><h3>No active Life Groups yet</h3><p class="muted">Please check again later or speak to your local church youth leader.</p></div>
@endforelse
</div>
<div class="pagination">{{ $groups->links() }}</div>
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.group-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.group-card{display:flex;flex-direction:column;gap:14px}.group-top{display:flex;gap:12px;align-items:center}.group-icon{width:44px;height:44px;flex:0 0 44px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center}.group-top h2{font-size:1.1rem;margin:0}.group-top p{margin:2px 0 0;color:var(--muted);font-size:.8rem}.group-meta{display:flex;flex-direction:column;gap:7px;font-size:.82rem;color:#475467}.group-meta i{width:18px;color:var(--primary)}.group-card form{margin-top:auto}.group-card .btn{width:100%}.form-errors{margin-bottom:18px;padding:13px 15px;border-radius:10px;background:#fff1f0;border:1px solid #f5b7b1;color:#8a1c16}@media(max-width:900px){.group-grid{grid-template-columns:1fr 1fr}}@media(max-width:620px){.group-grid{grid-template-columns:1fr}}
</style>
@endsection
