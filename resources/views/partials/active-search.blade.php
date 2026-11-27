{{-- Shown on a list page opened from the header search's "See all" link, so the
     filter is visible and easy to clear. Usage: @include('partials.active-search', ['route' => 'public.events']) --}}
@if(request()->filled('q'))
<p class="active-search">
    <span>Showing results for <strong>"{{ \Illuminate\Support\Str::limit(trim((string) request('q')), 60) }}"</strong></span>
    <a href="{{ route($route) }}"><i class="fas fa-xmark" aria-hidden="true"></i> Clear</a>
</p>
@once
<style>
.active-search{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 20px;color:var(--ink-soft)}
.active-search strong{color:var(--ink)}
.active-search a{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;background:var(--paper-2);color:var(--ink);font-weight:700;font-size:.88rem}
.active-search a:hover{background:var(--ink);color:#fff}
</style>
@endonce
@endif
