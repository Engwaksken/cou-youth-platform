@extends('layouts.app')
@section('content')
<div class="card" style="max-width:560px;margin:2rem auto;padding:1.5rem">
  <h1>{{ $event->title }}</h1>
  <p>{{ $event->description }}</p>
  <p><strong>Venue:</strong> {{ $event->venue ?: '—' }} | <strong>Date:</strong> {{ optional($event->starts_at)->format('d M Y H:i') }}</p>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <form method="POST" action="{{ route('public.event.register.store', $event->qr_token) }}">
    @csrf
    <label>Full name<input name="name" required placeholder="Your full name" value="{{ old('name') }}"></label>
    @error('name')<small>{{ $message }}</small>@enderror
    <label>Phone<input name="phone" placeholder="e.g. 0770000000" value="{{ old('phone') }}"></label>
    <label>Email<input name="email" type="email" placeholder="you@example.com" value="{{ old('email') }}"></label>
    <button class="btn btn-primary" type="submit">Register</button>
  </form>
</div>
@endsection
