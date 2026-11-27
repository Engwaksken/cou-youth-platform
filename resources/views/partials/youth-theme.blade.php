{{--
    Shared look for everything young people see: the public site and the youth portal.
    Included last in <head>, so it deliberately overrides the older per-layout styles.
    Brand colours still come from settings (--primary / --secondary); the warm paper,
    sunflower and coral accents give it a hand-made feel rather than a stock dashboard look.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:ital,wght@0,400..800;1,400..600&display=swap" rel="stylesheet">
<style>
:root{
    --ink:#1d1530;--ink-soft:#4a4160;--paper:#fbf6ee;--paper-2:#f3ebdd;--line:#e6dccb;
    --sun:#ffc23d;--sun-soft:#fff1c7;--coral:#ff6b4a;--coral-soft:#ffe3da;--leaf:#178a5e;--leaf-soft:#d9f3e6;--sky-soft:#dfeeff;
    --bg:var(--paper);--surface:#fff;--surface-soft:#fffaf2;--text:var(--ink);--muted:#6b6378;--border:var(--line);
    --primary-soft:color-mix(in srgb,var(--primary) 9%,#fff);--primary-light:color-mix(in srgb,var(--primary) 16%,#fff);
    --font-display:'Bricolage Grotesque',ui-sans-serif,system-ui,sans-serif;--font-body:'Figtree',ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;
    --r-card:20px;--r-btn:999px;--lift:4px 4px 0 var(--ink);
}
html,body{background:var(--bg)}
body{font-family:var(--font-body);color:var(--text);line-height:1.55}
body.dyslexia{font-family:Verdana,Tahoma,sans-serif}
h1,h2,h3,.display{font-family:var(--font-display);letter-spacing:-.02em;color:var(--ink);font-weight:750}
::selection{background:var(--sun);color:var(--ink)}

/* Page headers: big, friendly, sentence-case. */
.hero{padding:8px 0 26px}
.hero h1{font-family:var(--font-display);font-size:clamp(2.1rem,5vw,3.3rem);line-height:1.02;letter-spacing:-.035em;margin:6px 0 12px;color:var(--ink);font-weight:800}
.hero p{font-size:1.08rem;color:var(--ink-soft);max-width:40rem}
.scribble{position:relative;white-space:nowrap;z-index:0}
.scribble::after{content:'';position:absolute;left:-2%;right:-2%;bottom:-.12em;height:.42em;z-index:-1;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 20' preserveAspectRatio='none'%3E%3Cpath d='M3 14 C 40 4, 80 18, 120 9 S 180 6, 197 11' fill='none' stroke='%23ffc23d' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E") center/100% 100% no-repeat}

/* Eyebrows: a highlighter swipe in sentence case instead of shouty spaced capitals. */
.section-kicker{display:inline-block;text-transform:lowercase;letter-spacing:0!important;font-weight:700!important;font-size:.92rem!important;color:var(--ink)!important;padding:0 .2em;margin-bottom:6px;background:linear-gradient(var(--sun-soft),var(--sun-soft)) 0 88%/100% .55em no-repeat}
.section-kicker::first-letter{text-transform:uppercase}
.section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:16px;flex-wrap:wrap}
.section-head h2{font-size:clamp(1.35rem,2.6vw,1.75rem);margin:0}
.section-head p{margin:4px 0 0}

/* Cards feel like paper cut-outs: crisp edge, and a solid offset shadow when you can tap them. */
.card{background:var(--surface);border:1.5px solid var(--line)!important;border-radius:var(--r-card)!important;box-shadow:none;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.card:hover{transform:none;box-shadow:none;border-color:color-mix(in srgb,var(--ink) 25%,var(--line))!important}
a.card:hover,.card.is-tappable:hover,.clickable-card:hover{transform:translate(-2px,-2px);box-shadow:var(--lift);border-color:var(--ink)!important}
.card h3{font-size:1.15rem;line-height:1.25}
.muted{color:var(--muted)}

/* Buttons: pills you want to press. */
body .btn{border-radius:var(--r-btn);font-weight:700;border:1.5px solid var(--ink);background:#fff;color:var(--ink);box-shadow:none;padding-inline:18px;gap:8px}
body .btn:hover,body .btn:focus-visible{background:var(--ink);border-color:var(--ink);color:#fff;transform:translateY(-1px);box-shadow:none}
body .btn.btn-primary{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:0 3px 0 color-mix(in srgb,var(--primary) 55%,#000)}
body .btn.btn-primary:hover,body .btn.btn-primary:focus-visible{background:var(--primary);border-color:var(--primary);color:#fff;filter:brightness(1.08);box-shadow:0 4px 0 color-mix(in srgb,var(--primary) 55%,#000)}
body .btn.btn-primary:active{transform:translateY(2px);box-shadow:0 1px 0 color-mix(in srgb,var(--primary) 55%,#000)}
body .btn.btn-sun{background:var(--sun);border-color:var(--ink);color:var(--ink);box-shadow:0 3px 0 var(--ink)}
body .btn.btn-sun:hover,body .btn.btn-sun:focus-visible{background:#ffcd5c;color:var(--ink);box-shadow:0 4px 0 var(--ink)}
body .btn.btn-sun:active{transform:translateY(2px);box-shadow:0 1px 0 var(--ink)}
body .btn.btn-ghost{border-color:transparent;background:transparent;color:var(--primary)}
body .btn.btn-ghost:hover{background:var(--primary-soft);color:var(--primary)}

.badge{border-radius:999px;background:var(--paper-2);color:var(--ink);font-weight:700;font-size:.76rem;padding:4px 10px;border:1px solid var(--line)}
.badge.is-new,.badge-new{background:var(--coral);border-color:var(--coral);color:#fff}
.badge-good{background:var(--leaf-soft);border-color:transparent;color:var(--leaf)}
.badge-sun{background:var(--sun-soft);border-color:transparent;color:#7a5200}

.flash{border-radius:16px;border:1.5px solid var(--ink);background:var(--leaf-soft);color:var(--ink);font-weight:600;box-shadow:3px 3px 0 var(--ink)}

/* Date tiles, progress, chips and empty states used across pages. */
.date-tile{display:grid;place-items:center;align-content:center;width:58px;height:62px;flex:0 0 58px;border-radius:14px;background:var(--sun-soft);border:1.5px solid var(--ink);color:var(--ink);line-height:1;text-align:center}
.date-tile b{font-family:var(--font-display);font-size:1.45rem;font-weight:800}
.date-tile small{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-top:3px}
.meter{height:10px;border-radius:999px;background:var(--paper-2);overflow:hidden;border:1px solid var(--line)}
.meter>span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--primary),color-mix(in srgb,var(--primary) 60%,var(--coral)));transition:width .8s cubic-bezier(.22,1,.36,1)}
.chip{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;background:var(--paper-2);color:var(--ink-soft);font-size:.82rem;font-weight:600}
.chip i{color:var(--primary)}
.empty-card,.empty-state,.empty-note{text-align:center;padding:34px 20px!important;background:#fffdf9!important;border:1.5px dashed color-mix(in srgb,var(--ink) 22%,var(--line))!important;border-radius:var(--r-card)!important}
.empty-card h2,.empty-card h3,.empty-state h3{font-size:1.2rem;margin:6px 0}
.empty-card .btn,.empty-state .btn{margin-top:12px}
.empty-emoji{font-size:2rem;display:block;margin-bottom:4px}

/* Tabs */
.tabs .tab,.calendar-tabs .tab{border-radius:999px!important;border:1.5px solid transparent!important;font-weight:700;color:var(--ink-soft)}
.tabs .tab.active,.calendar-tabs .tab.active{background:var(--ink)!important;color:#fff!important;border-color:var(--ink)!important}
.tabs,.calendar-tabs{border-bottom:0!important;gap:6px!important}

/* Pagination from Laravel's Tailwind view */
.pagination nav [aria-current="page"] span{background:var(--ink)!important;color:#fff!important;border-color:var(--ink)!important}

/* Old four-up stat strips are no longer used on youth pages; hide any left over. */
.page-stats{display:none!important}

@media(prefers-reduced-motion:reduce){a.card:hover,.card.is-tappable:hover,.clickable-card:hover{transform:none}}
@media(hover:none){a.card:hover,.card.is-tappable:hover,.clickable-card:hover{box-shadow:none;transform:none}}
</style>
