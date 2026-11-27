@extends('youth.layout')
@section('title', 'Watch & Listen | COU Youth')
@section('youth_content')
@php($live = $services->firstWhere('status', 'live'))
<section class="hero">
    <h1>Watch &amp; <span class="scribble">listen</span></h1>
    <p>Online services, talks, worship and resources from across the Church of Uganda youth family.</p>
</section>

@if($live)
<a class="live-banner" href="{{ $live->stream_url }}" target="_blank" rel="noopener noreferrer">
    <span class="live-pill"><span class="live-dot"></span> Live now</span>
    <strong>{{ $live->title }}</strong>
    <span class="live-meta">@if(!empty($live->speaker)){{ $live->speaker }}, @endif{{ $live->platform ?? 'Online' }}</span>
    <span class="btn btn-sun"><i class="fas fa-play"></i> Join in</span>
</a>
@endif

@if($services->where('status', '!=', 'live')->isNotEmpty())
<section class="media-section">
    <div class="section-head"><h2>Services</h2></div>
    <div class="service-grid">
        @foreach($services->where('status', '!=', 'live') as $service)
            @php($starts = \Illuminate\Support\Carbon::parse($service->starts_at))
            <a class="card service-card" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">
                <span class="thumb">
                    @if(!empty($service->thumbnail_path))<img src="{{ asset('storage/'.$service->thumbnail_path) }}" alt="">@else<i class="fas fa-video" aria-hidden="true"></i>@endif
                    <span class="play" aria-hidden="true"><i class="fas fa-play"></i></span>
                </span>
                <span class="service-body">
                    <span class="badge {{ $service->status === 'scheduled' ? 'badge-sun' : '' }}">{{ $service->status === 'scheduled' ? $starts->format('D j M, g:i A') : 'Catch up' }}</span>
                    <strong>{{ $service->title }}</strong>
                    @if(!empty($service->speaker))<small>{{ $service->speaker }}</small>@endif
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="media-section">
    <div class="section-head"><h2>Library</h2></div>
    <div class="media-grid">
        @forelse($media as $item)
            <a class="card media-card" href="{{ asset('storage/'.$item->path) }}" target="_blank" rel="noopener noreferrer">
                <span class="thumb {{ $item->type }}">
                    @if($item->type === 'image')
                        <img src="{{ asset('storage/'.$item->path) }}" alt="{{ $item->alt_text ?: $item->title }}">
                    @else
                        <i class="fas {{ $item->type === 'video' ? 'fa-film' : ($item->type === 'audio' ? 'fa-headphones' : 'fa-file-lines') }}" aria-hidden="true"></i>
                    @endif
                </span>
                <span class="media-body">
                    <small>{{ ['video' => 'Video', 'audio' => 'Listen', 'image' => 'Photo', 'document' => 'Read'][$item->type] ?? ucfirst($item->type) }}</small>
                    <strong>{{ $item->title }}</strong>
                </span>
            </a>
        @empty
            <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-headphones"></i></span><h3>The library's still filling up</h3><p class="muted">Talks, worship sets and study notes will appear here as they're shared.</p></div>
        @endforelse
    </div>
    <div class="pagination">{{ $media->links() }}</div>
</section>

<style>
.media-section{margin-bottom:38px}
.live-banner{display:grid;grid-template-columns:auto minmax(0,1fr) auto;grid-template-rows:auto auto;column-gap:18px;align-items:center;padding:20px 22px;margin-bottom:30px;border-radius:var(--r-card);background:var(--ink);color:#fff;transition:transform .18s ease,box-shadow .18s ease}
.live-banner:hover{transform:translate(-2px,-2px);box-shadow:4px 4px 0 var(--coral)}
.live-banner .live-pill{grid-row:span 2}
.live-banner strong{font-family:var(--font-display);font-size:1.4rem;line-height:1.2}
.live-banner .live-meta{color:rgba(255,255,255,.7);grid-column:2}
.live-banner .btn{grid-row:1/3;grid-column:3}
.live-pill{display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:999px;background:var(--coral);color:#fff;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.live-dot{width:8px;height:8px;border-radius:50%;background:#fff;animation:pulse 1.4s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.service-grid,.media-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.media-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
.service-card,.media-card{padding:0;overflow:hidden;display:flex;flex-direction:column}
.thumb{position:relative;display:grid;place-items:center;aspect-ratio:16/10;background:var(--primary-soft);color:var(--primary);font-size:2rem;overflow:hidden}
.thumb img{width:100%;height:100%;object-fit:cover;display:block}
.thumb.audio{background:var(--sun-soft);color:#8a5a00}
.thumb.document{background:var(--leaf-soft);color:var(--leaf)}
.play{position:absolute;width:48px;height:48px;border-radius:50%;display:grid;place-items:center;background:#fff;color:var(--ink);font-size:1rem;border:var(--bw) solid var(--edge);transition:transform .18s ease}
.service-card:hover .play{transform:scale(1.1)}
.service-body,.media-body{display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:14px 16px 18px}
.service-body strong,.media-body strong{font-size:1.02rem;line-height:1.3}
.service-body small{color:var(--muted)}
.media-body small{font-weight:800;color:var(--primary);font-size:.78rem}
.empty-card{grid-column:1/-1}
@media(max-width:1100px){.media-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:900px){.service-grid,.media-grid{grid-template-columns:1fr 1fr}}
@media(max-width:620px){
    .service-grid{grid-template-columns:1fr}
    .live-banner{grid-template-columns:1fr;row-gap:8px}.live-banner .live-pill{grid-row:auto;justify-self:start}.live-banner .live-meta,.live-banner .btn{grid-column:auto;grid-row:auto}
}
@media(prefers-reduced-motion:reduce){.live-dot{animation:none}}
</style>
@endsection
