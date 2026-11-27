{{-- Header search: an icon that opens a search popup. It's a real link to the
     search page, so it still works if JavaScript doesn't load. Press "/" to open. --}}
<a class="search-trigger" href="{{ route('public.search') }}" data-search-open aria-haspopup="dialog" aria-label="Search" title="Search (press /)">
    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
</a>
@once
<dialog class="search-dialog" id="searchDialog" aria-labelledby="searchDialogTitle">
    <h2 class="sr-only" id="searchDialogTitle">Search the site</h2>
    <form class="search-dialog-form" method="GET" action="{{ route('public.search') }}" role="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <label class="sr-only" for="searchDialogInput">Search events, courses, life groups, stories and churches</label>
        <input id="searchDialogInput" type="search" name="q" value="{{ request()->routeIs('public.search') ? request('q') : '' }}" placeholder="Search events, courses, groups..." maxlength="100" autocomplete="off" enterkeyhint="search">
        <button class="search-close" type="button" data-search-close aria-label="Close search"><i class="fas fa-xmark" aria-hidden="true"></i></button>
    </form>
    <div class="search-dialog-hints">
        <span>Or jump to</span>
        <a href="{{ route('public.events') }}"><i class="fas fa-ticket" aria-hidden="true"></i> Events</a>
        <a href="{{ route('public.courses') }}"><i class="fas fa-book-open" aria-hidden="true"></i> Courses</a>
        <a href="{{ route('public.life-groups') }}"><i class="fas fa-people-group" aria-hidden="true"></i> Life groups</a>
        <a href="{{ route('public.churches') }}"><i class="fas fa-church" aria-hidden="true"></i> Find a church</a>
    </div>
</dialog>
<style>
.search-trigger{display:grid;place-items:center;width:44px;height:44px;flex:0 0 44px;border-radius:50%;border:1.5px solid var(--line);background:#fff;color:var(--ink);transition:border-color .15s ease,transform .15s ease}
.search-trigger:hover,.search-trigger:focus-visible{border-color:var(--edge-strong);color:var(--ink)}
.search-dialog{width:min(40rem,calc(100vw - 24px));margin:12vh auto auto;padding:0;border:var(--bw) solid var(--edge);border-radius:24px;background:#fff;color:var(--ink);box-shadow:var(--pop);overflow:visible}
.search-dialog::backdrop{background:rgba(29,21,48,.45);backdrop-filter:blur(3px)}
.search-dialog[open]{animation:search-pop .18s cubic-bezier(.22,1,.36,1)}
@keyframes search-pop{from{opacity:0;transform:translateY(-8px) scale(.98)}to{opacity:1;transform:none}}
.search-dialog-form{position:relative;display:flex;align-items:center;margin:0;border-bottom:1.5px solid var(--line)}
.search-dialog-form>i{position:absolute;left:22px;color:var(--muted);pointer-events:none}
body .search-dialog-form input{flex:1;height:64px!important;min-height:64px!important;padding:0 8px 0 54px!important;border:0!important;border-radius:24px 24px 0 0!important;background:transparent;font-size:1.15rem!important;color:var(--ink);box-shadow:none!important;outline:none}
.search-close{width:44px;height:44px;margin-right:10px;border:0;border-radius:50%;background:var(--paper-2);color:var(--ink);cursor:pointer;display:grid;place-items:center}
.search-close:hover{background:var(--ink);color:#fff}
.search-dialog-hints{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:14px 18px 18px}
.search-dialog-hints span{font-size:.85rem;font-weight:700;color:var(--muted);margin-right:2px}
.search-dialog-hints a{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:999px;background:var(--paper-2);color:var(--ink);font-weight:650;font-size:.88rem}
.search-dialog-hints a i{color:var(--primary)}
.search-dialog-hints a:hover{background:var(--ink);color:#fff}
.search-dialog-hints a:hover i{color:var(--sun)}
.search-dialog .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:640px){.search-dialog{margin-top:12px}}
@media(prefers-reduced-motion:reduce){.search-dialog[open]{animation:none}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('searchDialog');
    if (!dialog || typeof dialog.showModal !== 'function') return; // fall back to the search page link
    const input = document.getElementById('searchDialogInput');
    let opener = null;
    const open = trigger => {
        opener = trigger || document.activeElement;
        if (!dialog.open) dialog.showModal();
        input.focus();
        input.select();
    };
    document.querySelectorAll('[data-search-open]').forEach(trigger => trigger.addEventListener('click', event => {
        event.preventDefault();
        open(trigger);
    }));
    dialog.querySelector('[data-search-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => { if (opener && typeof opener.focus === 'function') opener.focus(); });
    dialog.querySelector('form').addEventListener('submit', event => {
        if (input.value.trim() === '') { event.preventDefault(); input.focus(); }
    });
    document.addEventListener('keydown', event => {
        const typing = event.target.closest('input, textarea, select, [contenteditable="true"]');
        if (event.key === '/' && !typing && !event.ctrlKey && !event.metaKey && !event.altKey && !dialog.open) {
            event.preventDefault();
            open(null);
        }
    });
});
</script>
@endonce
