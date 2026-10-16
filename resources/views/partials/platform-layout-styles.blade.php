@include('partials.responsive-scale')
<style>
    img,video,svg{max-width:100%}
    a,a:hover,a:focus,a:active,a:visited{text-decoration:none!important}
    html,body{overflow-x:clip}
    main{overflow-wrap:anywhere}
    main input,main select,main textarea{max-width:100%;min-width:0}
    main iframe,main embed,main object{max-width:100%}
    .portal-content{display:flex;flex-direction:column;min-width:0}
    .portal-content>.main{flex:1}
    .table-wrap{max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}

    /* Page shell: full-bleed backgrounds, centred content capped per breakpoint. */
    body>.container,.site-header>.nav,.platform-footer-grid,.platform-footer-bottom-inner{width:100%;max-width:var(--content-max);margin-inline:auto}
    .portal-content>.main,.portal-content .platform-footer-grid,.portal-content .platform-footer-bottom-inner{width:100%;max-width:var(--portal-max);margin-inline:auto}
    body>.container,.site-header>.nav,.portal-content>.main,.platform-footer-grid,.platform-footer-bottom-inner,.top{padding-inline:var(--page-gutter)}

    /* Portal chrome (admin + youth): header height and sidebar width follow the scale. */
    .top{height:var(--top-h)}
    .top .brand-logo-shell{width:2.625rem;height:2.625rem;min-width:2.625rem}
    .top .icon-btn{width:2.375rem;height:2.375rem}
    .wrap{min-height:calc(100vh - var(--top-h));min-height:calc(100dvh - var(--top-h))}
    @media(min-width:801px){.wrap{grid-template-columns:var(--side-w) minmax(0,1fr)}.wrap>.side{top:var(--top-h);height:calc(100vh - var(--top-h));height:calc(100dvh - var(--top-h))}}
    .side-brand .brand-logo-shell{width:2.5rem;height:2.5rem;min-width:2.5rem}

    .platform-footer{margin-top:40px;background:#24183f;color:#fff;line-height:1.6;overflow-wrap:anywhere}
    .platform-footer-grid{display:grid;grid-template-columns:minmax(0,1.4fr) repeat(3,minmax(0,1fr));gap:2rem;padding-block:2.75rem}
    .platform-footer-column{min-width:0}
    .platform-footer-brand{display:flex;align-items:center;gap:12px;color:#fff;text-decoration:none}
    .platform-footer-brand img{flex-shrink:0;width:3rem;height:3rem;object-fit:contain;background:#fff;padding:3px;border-radius:10px}
    .platform-footer-column h2{font-size:1rem;font-weight:800;color:#fff;margin:0 0 12px}
    .platform-footer-column p{color:#ddd4ef;font-size:.94rem;margin:14px 0 0}
    .platform-footer-column ul{list-style:none;margin:0;padding:0}
    .platform-footer-column li{margin:0;padding:0}
    .platform-footer-column li a{display:inline-flex;align-items:center;min-height:2.75rem;padding:8px 0;color:#ddd4ef;text-decoration:none;font-size:.94rem}
    .platform-footer a:hover{color:#fff}
    .platform-footer a:focus-visible{outline:3px solid #ffbf47;outline-offset:4px;border-radius:3px}
    .platform-footer-bottom{border-top:1px solid #514366;background:rgba(0,0,0,.14)}
    .platform-footer-bottom-inner{display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px 24px;padding-block:18px}
    .platform-footer-bottom p{margin:0;font-size:.85rem;color:#ddd4ef}
    @media(max-width:1100px){.platform-footer-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:640px){
        .platform-footer-grid{grid-template-columns:minmax(0,1fr);gap:26px;padding-block:32px}
        .platform-footer-bottom-inner{flex-direction:column}
        .filters input,.filters select{min-width:0;width:100%}
        .chat-form input{min-width:0}
        main input,main select,main textarea{font-size:1rem}
    }

    /* Wide tables and modals stay inside the viewport on every device. */
    .modal-card{width:min(47.5rem,100%)}
    .modal-card.small{width:min(28.75rem,100%)}
    .modal-card.large{width:min(61.25rem,100%)}
    @media(max-height:500px) and (orientation:landscape){.modal-card{max-height:96vh;max-height:96dvh}}

    /* Hover lift effects only where a real pointer exists (avoids sticky hover on touch screens). */
    @media(hover:none){.card:hover,.btn:hover,.float-btn:hover,.nav-links a:hover{transform:none!important}}
    @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
