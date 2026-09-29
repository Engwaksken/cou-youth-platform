@extends('youth.layout')
@section('title', 'Annual Theme | COU Youth')
@section('youth_content')
@php
    $themeCount = $themes->count();
    $withScripture = $themes->filter(fn($theme) => !empty($theme->scripture_reference))->count();
    $withImages = $themes->filter(fn($theme) => !empty($theme->image_path))->count();
    $latestYear = $themes->max('year') ?: now()->year;
@endphp
<section class="hero"><span class="section-kicker">ANNUAL THEME</span><h1>Church of Uganda Youth Annual Theme</h1><p>Explore the published annual themes, Scripture references and focus areas guiding youth ministry.</p></section>
<div class="page-stats">
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-book-bible"></i></span><div><strong>{{ $themeCount }}</strong><span>Published themes</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-calendar"></i></span><div><strong>{{ $latestYear }}</strong><span>Latest theme year</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-book-open"></i></span><div><strong>{{ $withScripture }}</strong><span>With Scripture reference</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-image"></i></span><div><strong>{{ $withImages }}</strong><span>With theme image</span></div></div>
</div>
<div class="theme-grid">
@forelse($themes as $theme)
<article class="card theme-card">@if(!empty($theme->image_path))<img src="{{ asset('storage/'.$theme->image_path) }}" alt="{{ $theme->theme }}">@endif<div class="theme-body"><span class="theme-year">{{ $theme->year }}</span><h2>{{ $theme->theme }}</h2>@if(!empty($theme->scripture_reference))<p class="scripture"><i class="fas fa-book-bible"></i> {{ $theme->scripture_reference }}</p>@endif @if(!empty($theme->description))<p class="muted">{{ $theme->description }}</p>@endif</div></article>
@empty
<div class="card empty-card"><i class="fas fa-book-bible"></i><h2>No published annual theme yet</h2><p class="muted">The published annual theme will appear here when available.</p></div>
@endforelse
</div>
<style>.theme-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.theme-card{padding:0;overflow:hidden}.theme-card img{width:100%;height:220px;object-fit:cover}.theme-body{padding:22px}.theme-year{display:inline-flex;padding:5px 10px;border-radius:999px;background:var(--primary-light);color:var(--primary);font-weight:800}.theme-body h2{margin:12px 0 8px}.scripture{font-weight:800;color:var(--primary)}.empty-card{grid-column:1/-1;text-align:center;padding:36px}.empty-card>i{font-size:2rem;color:var(--primary)}@media(max-width:760px){.theme-grid{grid-template-columns:1fr}}</style>
@endsection
