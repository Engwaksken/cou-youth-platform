@extends('youth.layout')
@section('title', 'Annual Theme | COU Youth')
@section('youth_content')
@php
    $current = $themes->first();
    $past = $themes->slice(1);
@endphp
<section class="hero">
    <h1>This year's <span class="scribble">theme</span></h1>
    <p>Each year the Church of Uganda youth ministry rallies around one word from Scripture. Here's what we're living out together.</p>
</section>

@if($current)
<article class="theme-feature">
    @if(!empty($current->image_path))<img src="{{ asset('storage/'.$current->image_path) }}" alt="">@endif
    <div class="theme-body">
        <span class="theme-year">{{ $current->year }}</span>
        <h2>{{ $current->theme }}</h2>
        @if(!empty($current->scripture_reference))<p class="scripture"><i class="fas fa-book-bible"></i> {{ $current->scripture_reference }}</p>@endif
        @if(!empty($current->description))<p class="desc">{{ $current->description }}</p>@endif
    </div>
</article>
@else
<div class="card empty-card"><span class="empty-emoji" aria-hidden="true">📜</span><h3>This year's theme is on its way</h3><p class="muted">It'll be shared here as soon as it's announced.</p></div>
@endif

@if($past->isNotEmpty())
<section class="past">
    <div class="section-head"><h2>Looking back</h2></div>
    <div class="theme-grid">
        @foreach($past as $theme)
            <article class="card theme-card">
                <span class="theme-year small">{{ $theme->year }}</span>
                <h3>{{ $theme->theme }}</h3>
                @if(!empty($theme->scripture_reference))<p class="scripture"><i class="fas fa-book-bible"></i> {{ $theme->scripture_reference }}</p>@endif
            </article>
        @endforeach
    </div>
</section>
@endif

<style>
.theme-feature{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);border-radius:var(--r-card);overflow:hidden;background:var(--ink);color:#fff;margin-bottom:38px;border:1.5px solid var(--ink)}
.theme-feature img{width:100%;height:100%;min-height:280px;object-fit:cover;display:block}
.theme-feature:not(:has(img)){grid-template-columns:1fr}
.theme-body{padding:clamp(24px,4vw,44px);position:relative;overflow:hidden}
.theme-body::after{content:'';position:absolute;right:-60px;top:-60px;width:200px;height:200px;border-radius:50%;background:var(--sun);opacity:.18}
.theme-year{display:inline-block;padding:5px 14px;border-radius:999px;background:var(--sun);color:var(--ink);font-family:var(--font-display);font-weight:800}
.theme-year.small{padding:3px 10px;font-size:.85rem}
.theme-feature h2{color:#fff;font-size:clamp(1.8rem,4vw,2.8rem);line-height:1.08;letter-spacing:-.03em;margin:16px 0 12px}
.theme-feature .scripture{color:var(--sun);font-weight:800}
.theme-feature .desc{color:rgba(255,255,255,.82);font-size:1.05rem;max-width:38rem}
.theme-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.theme-card{padding:20px}
.theme-card h3{margin:10px 0 6px}
.theme-card .scripture{margin:0;color:var(--primary);font-weight:700}
@media(max-width:860px){.theme-feature{grid-template-columns:1fr}.theme-feature img{max-height:260px;min-height:0}.theme-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.theme-grid{grid-template-columns:1fr}}
</style>
@endsection
