{{--
    Shared look for everything young people see: the public site and the youth portal.
    Included last in <head>, so it deliberately overrides the older per-layout styles.
    Brand colours still come from settings (--primary / --secondary); the warm paper,
    sunflower and coral accents give it a hand-made feel rather than a stock dashboard look.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@php($look = app(\App\Services\Branding\AppearanceService::class)->data())
<link href="{{ $look['fonts_url'] }}" rel="stylesheet">
<style>
/* Colours, fonts and style come from Admin > Settings > Appearance. */
:root{
    --primary:{{ $look['primary_color'] }};--secondary:{{ $look['secondary_color'] }};--primary-2:{{ $look['secondary_color'] }};
    --ink:#1d1530;--ink-soft:#4a4160;
    --paper:{{ $look['background_color'] }};
    --paper-2:color-mix(in srgb,color-mix(in srgb,var(--paper) 90%,var(--sun)) 95%,var(--ink));
    --line:color-mix(in srgb,var(--paper),var(--ink) 10%);
    --sun:{{ $look['accent_color'] }};--sun-soft:color-mix(in srgb,var(--sun) 24%,#fff);
    --coral:#ff6b4a;--coral-soft:#ffe3da;--leaf:#178a5e;--leaf-soft:#d9f3e6;--sky-soft:#dfeeff;
    --bg:var(--paper);--surface:#fff;--surface-soft:color-mix(in srgb,var(--paper) 40%,#fff);--text:var(--ink);--muted:#6b6378;--border:var(--line);
    --primary-soft:color-mix(in srgb,var(--primary) 9%,#fff);--primary-light:color-mix(in srgb,var(--primary) 16%,#fff);
    --font-display:'{{ $look['heading_font'] }}',ui-sans-serif,system-ui,sans-serif;--font-body:'{{ $look['body_font'] }}',ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;
    --side-bg:{{ $look['sidebar_color'] ?? 'var(--paper-2)' }};--side-ink:{{ $look['sidebar_text'] ?? 'var(--ink)' }};
@switch($look['theme_style'])
@case('soft')
    /* Soft: rounded, gentle shadows, no outlines. */
    --bw:1px;--edge:color-mix(in srgb,var(--ink) 7%,transparent);--edge-strong:color-mix(in srgb,var(--ink) 14%,transparent);
    --pop:0 14px 30px rgba(29,21,48,.12);--press:0 6px 14px color-mix(in srgb,var(--primary) 25%,transparent);--press-up:0 8px 18px color-mix(in srgb,var(--primary) 30%,transparent);--press-down:0 2px 6px rgba(29,21,48,.12);
    --r-card:20px;--r-btn:999px;--tilt:.5;
@break
@case('clean')
    /* Clean: flat, light borders, smaller corners. */
    --bw:1px;--edge:var(--line);--edge-strong:color-mix(in srgb,var(--ink) 35%,var(--line));
    --pop:none;--press:none;--press-up:none;--press-down:none;
    --r-card:12px;--r-btn:10px;--tilt:0;
@break
@default
    /* Playful: bold outlines and solid offset shadows. */
    --bw:1.5px;--edge:var(--ink);--edge-strong:var(--ink);
    --pop:4px 4px 0 var(--ink);--press:0 3px 0 var(--ink);--press-up:0 4px 0 var(--ink);--press-down:0 1px 0 var(--ink);
    --r-card:16px;--r-btn:999px;--tilt:1;
@endswitch
    --lift:var(--pop);
}
:root{--btn-depth:0 3px 0 color-mix(in srgb,var(--primary) 55%,#000);--btn-depth-up:0 4px 0 color-mix(in srgb,var(--primary) 55%,#000);--btn-depth-down:0 1px 0 color-mix(in srgb,var(--primary) 55%,#000)}
@if($look['theme_style'] === 'soft'):root{--btn-depth:var(--press);--btn-depth-up:var(--press-up);--btn-depth-down:var(--press-down)}@elseif($look['theme_style'] === 'clean'):root{--btn-depth:none;--btn-depth-up:none;--btn-depth-down:none}@endif
html,body{background:var(--bg)}
body{font-family:var(--font-body);color:var(--text);line-height:1.55}
body.dyslexia{font-family:Verdana,Tahoma,sans-serif}
h1,h2,h3,.display{font-family:var(--font-display);letter-spacing:-.02em;color:var(--ink);font-weight:750}
::selection{background:var(--sun);color:var(--ink)}

/* Page headers: big, friendly, sentence-case. */
.hero{padding:2px 0 18px}
.hero h1{font-family:var(--font-display);font-size:clamp(1.65rem,3.6vw,2.4rem);line-height:1.08;letter-spacing:-.03em;margin:4px 0 8px;color:var(--ink);font-weight:800}
.hero p{font-size:1rem;color:var(--ink-soft);max-width:40rem;margin:0}
.scribble{position:relative;white-space:nowrap;z-index:0}
.scribble::after{content:'';position:absolute;left:-2%;right:-2%;bottom:-.12em;height:.42em;z-index:-1;background:var(--sun);-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 20' preserveAspectRatio='none'%3E%3Cpath d='M3 14 C 40 4, 80 18, 120 9 S 180 6, 197 11' fill='none' stroke='%23000' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E") center/100% 100% no-repeat;mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 20' preserveAspectRatio='none'%3E%3Cpath d='M3 14 C 40 4, 80 18, 120 9 S 180 6, 197 11' fill='none' stroke='%23000' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E") center/100% 100% no-repeat}

/* Eyebrows: a highlighter swipe in sentence case instead of shouty spaced capitals. */
.section-kicker{display:inline-block;text-transform:lowercase;letter-spacing:0!important;font-weight:700!important;font-size:.92rem!important;color:var(--ink)!important;padding:0 .2em;margin-bottom:6px;background:linear-gradient(var(--sun-soft),var(--sun-soft)) 0 88%/100% .55em no-repeat}
.section-kicker::first-letter{text-transform:uppercase}
.section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.section-head h2{font-size:clamp(1.1rem,1.8vw,1.3rem);margin:0}
.section-head p{margin:4px 0 0}

/* Cards feel like paper cut-outs: crisp edge, and a solid offset shadow when you can tap them. */
.card{background:var(--surface);border:1.5px solid var(--line)!important;border-radius:var(--r-card)!important;box-shadow:none;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.card:hover{transform:none;box-shadow:none;border-color:color-mix(in srgb,var(--ink) 25%,var(--line))!important}
a.card:hover,.card.is-tappable:hover,.clickable-card:hover{transform:translate(-2px,-2px);box-shadow:var(--lift);border-color:var(--edge-strong)!important}
.card{padding:16px}
.card h3{font-size:1.02rem;line-height:1.3}
.card h2{font-size:1.15rem}
.muted{color:var(--muted)}

/* Buttons: pills you want to press. */
body .btn{border-radius:var(--r-btn);font-weight:700;border:var(--bw) solid var(--edge);background:#fff;color:var(--ink);box-shadow:none;padding-inline:16px;gap:8px;font-size:.9rem}
body .btn:hover,body .btn:focus-visible{background:var(--ink);border-color:var(--edge-strong);color:#fff;transform:translateY(-1px);box-shadow:none}
body .btn.btn-primary{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:var(--btn-depth)}
body .btn.btn-primary:hover,body .btn.btn-primary:focus-visible{background:var(--primary);border-color:var(--primary);color:#fff;filter:brightness(1.08);box-shadow:var(--btn-depth-up)}
body .btn.btn-primary:active{transform:translateY(2px);box-shadow:var(--btn-depth-down)}
body .btn.btn-sun{background:var(--sun);border-color:var(--edge-strong);color:var(--ink);box-shadow:var(--press)}
body .btn.btn-sun:hover,body .btn.btn-sun:focus-visible{background:#ffcd5c;color:var(--ink);box-shadow:var(--press-up)}
body .btn.btn-sun:active{transform:translateY(2px);box-shadow:var(--press-down)}
body .btn.btn-ghost{border-color:transparent;background:transparent;color:var(--primary)}
body .btn.btn-ghost:hover{background:var(--primary-soft);color:var(--primary)}

.badge{border-radius:999px;background:var(--paper-2);color:var(--ink);font-weight:700;font-size:.76rem;padding:4px 10px;border:1px solid var(--line)}
.badge.is-new,.badge-new{background:var(--coral);border-color:var(--coral);color:#fff}
.badge-good{background:var(--leaf-soft);border-color:transparent;color:var(--leaf)}
.badge-sun{background:var(--sun-soft);border-color:transparent;color:#7a5200}

.flash{border-radius:16px;border:var(--bw) solid var(--edge);background:var(--leaf-soft);color:var(--ink);font-weight:600;box-shadow:var(--pop)}

/* Date tiles, progress, chips and empty states used across pages. */
.date-tile{display:grid;place-items:center;align-content:center;width:48px;height:52px;flex:0 0 48px;border-radius:12px;background:var(--sun-soft);border:var(--bw) solid var(--edge);color:var(--ink);line-height:1;text-align:center}
.date-tile b{font-family:var(--font-display);font-size:1.15rem;font-weight:800}
.date-tile small{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-top:3px}
.meter{height:10px;border-radius:999px;background:var(--paper-2);overflow:hidden;border:1px solid var(--line)}
.meter>span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--primary),color-mix(in srgb,var(--primary) 60%,var(--coral)));transition:width .8s cubic-bezier(.22,1,.36,1)}
.chip{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;background:var(--paper-2);color:var(--ink-soft);font-size:.82rem;font-weight:600}
.chip i{color:var(--primary)}
.empty-card,.empty-state,.empty-note{text-align:center;padding:22px 16px!important;background:#fffdf9!important;border:1.5px dashed color-mix(in srgb,var(--ink) 22%,var(--line))!important;border-radius:var(--r-card)!important}
.empty-card h2,.empty-card h3,.empty-state h3{font-size:1.05rem;margin:4px 0}
.empty-card .btn,.empty-state .btn{margin-top:12px}
.empty-emoji{width:44px;height:44px;margin:0 auto 8px;border-radius:14px;display:grid;place-items:center;background:var(--sun-soft);color:var(--ink);font-size:1.05rem;border:var(--bw) solid var(--edge);transform:rotate(calc(-4deg * var(--tilt)))}

/* Tabs */
.tabs .tab,.calendar-tabs .tab{border-radius:var(--r-btn)!important;border:var(--bw) solid transparent!important;font-weight:700;color:var(--ink-soft)}
.tabs .tab.active,.calendar-tabs .tab.active{background:var(--ink)!important;color:#fff!important;border-color:var(--edge-strong)!important}
.tabs,.calendar-tabs{border-bottom:0!important;gap:6px!important}

/* Pagination from Laravel's Tailwind view */
.pagination nav [aria-current="page"] span{background:var(--ink)!important;color:#fff!important;border-color:var(--edge-strong)!important}

/* Old four-up stat strips are no longer used on youth pages; hide any left over. */
.page-stats{display:none!important}

@media(prefers-reduced-motion:reduce){a.card:hover,.card.is-tappable:hover,.clickable-card:hover{transform:none}}
@media(hover:none){a.card:hover,.card.is-tappable:hover,.clickable-card:hover{box-shadow:none;transform:none}}
</style>
