{{--
    Accessible tabs for forms split into sections. Markup:
      <div data-tabset>
        <div class="tabset-list" role="tablist"><button role="tab" data-tab="a" aria-controls="tab-a">...</button>...</div>
        <section class="tabset-panel" role="tabpanel" id="tab-a">...</section>...
      </div>
    Arrow keys move between tabs. If the browser blocks a submit because a field on a hidden
    tab is invalid (for example an empty required field), that tab opens so the person can see it.
--}}
@once
<style>
.tabset-list{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;padding:5px;border-radius:calc(var(--r-btn) + 4px);background:var(--paper-2);width:fit-content;max-width:100%}
.tabset-list [role=tab]{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border:0;border-radius:var(--r-btn);background:transparent;color:var(--ink-soft);font-weight:700;cursor:pointer;white-space:nowrap}
.tabset-list [role=tab]:hover{color:var(--ink);background:color-mix(in srgb,#fff 60%,transparent)}
.tabset-list [role=tab][aria-selected=true]{background:#fff;color:var(--ink);box-shadow:0 1px 3px rgba(29,21,48,.12)}
.tabset-list [role=tab] i{color:var(--primary)}
.tabset-list .tab-flag{width:8px;height:8px;border-radius:50%;background:var(--coral)}
.tabset-panel[hidden]{display:none}
@media(max-width:560px){.tabset-list{width:100%}.tabset-list [role=tab]{flex:1;justify-content:center;padding-inline:10px}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tabset]').forEach(set => {
        const tabs = [...set.querySelectorAll('[role=tab]')];
        const show = (tab, focus = false) => {
            tabs.forEach(t => {
                const on = t === tab;
                t.setAttribute('aria-selected', String(on));
                t.tabIndex = on ? 0 : -1;
                document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
            });
            if (focus) tab.focus();
        };
        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => show(tab));
            tab.addEventListener('keydown', e => {
                const step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
                if (step) { e.preventDefault(); show(tabs[(i + step + tabs.length) % tabs.length], true); }
                if (e.key === 'Home') { e.preventDefault(); show(tabs[0], true); }
                if (e.key === 'End') { e.preventDefault(); show(tabs[tabs.length - 1], true); }
            });
        });
        const form = set.closest('form');
        if (form) {
            form.addEventListener('invalid', e => {
                const panel = e.target.closest('[role=tabpanel]');
                const tab = panel && tabs.find(t => t.getAttribute('aria-controls') === panel.id);
                if (tab && panel.hidden) show(tab);
            }, true);
        }
        show(tabs.find(t => t.getAttribute('aria-selected') === 'true') || tabs[0]);
    });
});
</script>
@endonce
