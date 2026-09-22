@extends('public.layout')
@section('title','Events | COU Youth')
@section('content')
<section class="hero"><h1>Youth Events</h1><p>Upcoming worship, fellowship, discipleship and mission activities.</p></section>
<form class="filters" method="get"><input name="q" value="{{ request('q') }}" placeholder="Search events or venue"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button></form>
<div class="grid">@forelse($items as $event)<article class="card"><span class="badge">{{ ucfirst($event->status) }}</span><h3 style="margin-top:12px">{{ $event->title }}</h3><p><i class="fas fa-calendar"></i> {{ optional($event->starts_at)->format('d M Y H:i') ?: 'Date to be confirmed' }}</p><p class="muted"><i class="fas fa-location-dot"></i> {{ $event->venue ?: 'Venue to be confirmed' }}</p></article>@empty<div class="card">No upcoming events found.</div>@endforelse</div><div class="pagination">{{ $items->links() }}</div>
@endsection
