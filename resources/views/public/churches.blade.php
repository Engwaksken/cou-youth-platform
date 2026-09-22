@extends('public.layout')
@section('title','Church Locator | COU Youth')
@section('content')
<section class="hero"><h1>Church Locator</h1><p>Find Church of Uganda congregations, service times and youth fellowship information.</p></section>
<form class="filters" method="get"><input name="q" value="{{ request('q') }}" placeholder="Search church or location"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button></form>
<div class="grid">@forelse($items as $church)<article class="card"><h3>{{ $church->name }}</h3><p class="muted">{{ $church->organisationUnit?->name }}</p><p><i class="fas fa-location-dot"></i> {{ $church->address ?: 'Address not yet provided' }}</p><p><i class="fas fa-people-group"></i> {{ $church->youth_fellowship_times ?: 'Youth fellowship times to be confirmed' }}</p>@if($church->phone)<p><i class="fas fa-phone"></i> {{ $church->phone }}</p>@endif</article>@empty<div class="card">No church locations found.</div>@endforelse</div><div class="pagination">{{ $items->links() }}</div>
@endsection
