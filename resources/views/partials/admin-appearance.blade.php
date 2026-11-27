{{-- Applies Admin > Settings > Appearance to the admin area: fonts, and the sidebar
     background when one is chosen (text switches to dark or white to stay readable). --}}
@php($look = app(\App\Services\Branding\AppearanceService::class)->data())
<link href="{{ $look['fonts_url'] }}" rel="stylesheet">
<style>
body{font-family:'{{ $look['body_font'] }}','DM Sans',Arial,sans-serif}
h1,h2,h3,.page-head h1,.brand{font-family:'{{ $look['heading_font'] }}','DM Sans',Arial,sans-serif}
@if($look['sidebar_color'])
:root{--side-bg:{{ $look['sidebar_color'] }};--side-ink:{{ $look['sidebar_text'] }}}
.side{background:var(--side-bg);color:var(--side-ink);scrollbar-color:color-mix(in srgb,var(--side-ink) 30%,transparent) transparent}
.side-brand{border-color:color-mix(in srgb,var(--side-ink) 20%,transparent);background:color-mix(in srgb,var(--side-ink) 8%,transparent)}
.side-brand-copy strong,.side-brand-copy small{color:var(--side-ink)}
.side a:not(:hover):not(.active),.side a:not(:hover):not(.active) i,.nav-group summary{color:var(--side-ink)}
.nav-label{color:color-mix(in srgb,var(--side-ink) 60%,transparent)}
.nav-group summary:hover,.nav-group summary:focus-visible{background:color-mix(in srgb,var(--side-ink) 12%,transparent);outline-color:color-mix(in srgb,var(--side-ink) 45%,transparent)}
@endif
</style>
