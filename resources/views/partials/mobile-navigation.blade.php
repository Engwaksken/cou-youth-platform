<style>
    .branded-menu-toggle{display:none}
    .mobile-nav-backdrop{display:none}
    @media(max-width:800px){
        .top{gap:10px}
        .top-left{flex:1;min-width:0}
        .top-left>.brand{display:none}
        .top-actions{flex-shrink:0}
        .top-actions .btn{min-height:44px;padding:9px}
        .logout-label{display:none}
        .branded-menu-toggle{display:flex;align-items:center;gap:9px;width:100%;min-width:0;min-height:48px;padding:4px 8px;border:1px solid var(--border);border-radius:12px;background:#fff;color:var(--primary);font:inherit;font-weight:800;text-align:left;cursor:pointer}
        .branded-menu-toggle:focus-visible{outline:3px solid var(--secondary);outline-offset:2px}
        .branded-menu-toggle .brand-logo-shell{width:36px;height:36px;min-width:36px;box-shadow:none;border-radius:9px}
        .menu-brand-title{flex:1;min-width:0;font-size:clamp(.75rem,3vw,.95rem);line-height:1.25;overflow-wrap:anywhere;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
        .menu-toggle-icon{flex-shrink:0;font-size:1.1rem}
        .side.open{display:block;position:fixed;top:68px;left:0;bottom:0;width:min(300px,calc(100vw - 32px));height:calc(100dvh - 68px);z-index:90;box-shadow:18px 0 45px rgba(0,0,0,.25);overscroll-behavior:contain}
        .mobile-nav-backdrop.open{display:block;position:fixed;inset:68px 0 0;background:rgba(15,23,42,.5);z-index:80}
        body.mobile-nav-open{overflow:hidden}
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    if (!toggle) return;
    const sidebar = document.getElementById(toggle.getAttribute('aria-controls'));
    const backdrop = document.querySelector('[data-mobile-nav-backdrop]');
    if (!sidebar || !backdrop) return;
    const mobile = window.matchMedia('(max-width: 800px)');
    const setOpen = (open, restoreFocus = false) => {
        open = open && mobile.matches;
        sidebar.classList.toggle('open', open);
        backdrop.classList.toggle('open', open);
        document.body.classList.toggle('mobile-nav-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', `${open ? 'Close' : 'Open'} navigation for ${toggle.dataset.menuTitle}`);
        toggle.querySelector('.menu-toggle-icon').classList.toggle('fa-bars', !open);
        toggle.querySelector('.menu-toggle-icon').classList.toggle('fa-xmark', open);
        if (restoreFocus && mobile.matches) toggle.focus();
    };
    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    backdrop.addEventListener('click', () => setOpen(false, true));
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') setOpen(false, true);
    });
    mobile.addEventListener('change', () => setOpen(false));
});
</script>
