@extends('public.layout')
@section('title','News & Resources | COU Youth')
@section('content')
<section class="hero"><h1>News & Resources</h1><p>Latest announcements, devotions, opportunities and youth ministry updates.</p></section>
<form class="filters" method="get"><input name="q" value="{{ request('q') }}" placeholder="Search news and resources"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button></form>
<div class="grid">@forelse($items as $item)<article class="card"><span class="badge">{{ ucwords(str_replace('_',' ',$item->type)) }}</span><h3 style="margin-top:12px">{{ $item->title }}</h3><p class="muted">{{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body),160) }}</p><small class="muted">{{ optional($item->published_at ?: $item->created_at)->format('d M Y') }}</small></article>@empty<div class="card">No published content found.</div>@endforelse</div><div class="pagination">{{ $items->links() }}</div>
@endsection
