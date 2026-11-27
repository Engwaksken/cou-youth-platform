<form class="header-search" method="GET" action="{{ route('public.search') }}" role="search">
    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
    <label class="sr-only" for="headerSearch">Search the site</label>
    <input id="headerSearch" type="search" name="q" value="{{ request()->routeIs('public.search') ? request('q') : '' }}" placeholder="Search events, courses…" maxlength="100" autocomplete="off">
</form>
@once
<style>
.header-search{position:relative;margin:0}
.header-search>i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem;pointer-events:none}
body .header-search input{width:min(15rem,22vw);height:42px!important;min-height:42px!important;padding:0 14px 0 38px!important;border:1.5px solid var(--line);border-radius:999px!important;background:#fff;color:var(--ink);font-size:.92rem!important;transition:width .2s ease,border-color .15s ease}
body .header-search input:focus{width:min(20rem,30vw);border-color:var(--ink);outline:none;box-shadow:0 0 0 4px var(--sun-soft)}
.header-search .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
</style>
@endonce
