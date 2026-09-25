@extends('public.layout')
@section('title', 'Media & Online Services | COU Youth')
@section('content')
<section class="hero"><span class="section-kicker">MEDIA & WORSHIP</span><h1>Media and online services</h1><p>Watch published youth media, join live services and revisit completed online sessions.</p></section>

@if($services->isNotEmpty())
<section class="media-section">
<div class="section-head"><div><span class="section-kicker">ONLINE SERVICES</span><h2>Live and scheduled</h2></div></div>
<div class="service-grid">
@foreach($services as $service)
<article class="card service-card">
@if($service->thumbnail_path)<img src="{{ asset('storage/'.$service->thumbnail_path) }}" alt="">@else<div class="service-placeholder"><i class="fas fa-video"></i></div>@endif
<div class="service-body"><div class="service-heading"><h3>{{ $service->title }}</h3><span class="badge">{{ ucfirst($service->status) }}</span></div>
<p class="muted">{{ \Illuminate\Support\Str::limit($service->description ?? '',150) }}</p>
<div class="service-meta"><span><i class="fas fa-calendar"></i> {{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('D, d M Y · H:i') }}</span>@if($service->speaker)<span><i class="fas fa-user"></i> {{ $service->speaker }}</span>@endif@if($service->platform)<span><i class="fas fa-display"></i> {{ $service->platform }}</span>@endif</div>
<a class="btn btn-primary" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-play"></i> {{ $service->status === 'live' ? 'Join live' : 'Open service' }}</a></div>
</article>
@endforeach
</div>
</section>
@endif

<section class="media-section">
<div class="section-head"><div><span class="section-kicker">MEDIA LIBRARY</span><h2>Published resources</h2></div></div>
<div class="media-grid">
@forelse($media as $item)
<article class="card media-card">
@if(data_get($item,'thumbnail_path'))<img src="{{ asset('storage/'.data_get($item,'thumbnail_path')) }}" alt="">@else<div class="media-placeholder"><i class="fas fa-photo-film"></i></div>@endif
<div class="media-body"><span class="badge">{{ ucfirst((string) data_get($item,'media_type','Media')) }}</span><h3>{{ data_get($item,'title','Youth media') }}</h3>@if(data_get($item,'description'))<p class="muted">{{ \Illuminate\Support\Str::limit(data_get($item,'description'),140) }}</p>@endif
@php $mediaUrl = data_get($item,'external_url') ?: data_get($item,'file_url'); @endphp
@if($mediaUrl)<a class="btn" href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer">Open resource</a>@elseif(data_get($item,'file_path'))<a class="btn" href="{{ asset('storage/'.data_get($item,'file_path')) }}" target="_blank" rel="noopener noreferrer">Open resource</a>@endif</div>
</article>
@empty
<div class="card empty-card"><h3>No published media yet</h3><p class="muted">Published youth resources will appear here.</p></div>
@endforelse
</div>
<div class="pagination">{{ $media->links() }}</div>
</section>
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.media-section{margin-bottom:36px}.section-head{margin-bottom:15px}.section-head h2{margin:2px 0 0}.service-grid,.media-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.service-card,.media-card{padding:0;overflow:hidden;display:flex;flex-direction:column}.service-card img,.media-card img{width:100%;height:165px;object-fit:cover;border-radius:0}.service-placeholder,.media-placeholder{height:165px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center;font-size:2rem}.service-body,.media-body{padding:17px;display:flex;flex-direction:column;gap:11px;flex:1}.service-heading{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.service-heading h3,.media-body h3{margin:0}.service-meta{display:flex;flex-direction:column;gap:5px;font-size:.8rem;color:#475467}.service-meta i{width:17px;color:var(--primary)}.service-body .btn,.media-body .btn{margin-top:auto}.empty-card{grid-column:1/-1;text-align:center;padding:30px}@media(max-width:900px){.service-grid,.media-grid{grid-template-columns:1fr 1fr}}@media(max-width:620px){.service-grid,.media-grid{grid-template-columns:1fr}}
</style>
@endsection
