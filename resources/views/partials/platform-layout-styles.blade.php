<style>
    :root{--page-gutter:24px}
    @media(min-width:1200px){:root{--page-gutter:32px}}
    @media(max-width:640px){:root{--page-gutter:16px}}
    img,video,svg{max-width:100%}
    a,a:hover,a:focus,a:active,a:visited{text-decoration:none!important}
    main{overflow-wrap:anywhere}
    main input,main select,main textarea{max-width:100%;min-width:0}
    .portal-content{display:flex;flex-direction:column;min-width:0}
    .portal-content>.main{flex:1}
    .table-wrap{max-width:100%;overflow-x:auto}
    .platform-footer{margin-top:40px;background:#24183f;color:#fff;line-height:1.6;overflow-wrap:anywhere}
    .platform-footer-grid{display:grid;grid-template-columns:minmax(0,1.4fr) repeat(3,minmax(0,1fr));gap:32px;width:100%;margin:0 auto;padding:44px 24px}
    .platform-footer-column{min-width:0}
    .platform-footer-brand{display:flex;align-items:center;gap:12px;color:#fff;text-decoration:none}
    .platform-footer-brand img{flex-shrink:0;object-fit:contain;background:#fff;padding:3px;border-radius:10px}
    .platform-footer-column h2{font-size:1rem;font-weight:800;color:#fff;margin:0 0 12px}
    .platform-footer-column p{color:#ddd4ef;font-size:.94rem;margin:14px 0 0}
    .platform-footer-column ul{list-style:none;margin:0;padding:0}
    .platform-footer-column li{margin:0;padding:0}
    .platform-footer-column li a{display:inline-flex;align-items:center;min-height:44px;padding:8px 0;color:#ddd4ef;text-decoration:none;font-size:.94rem}
    .platform-footer a:hover{color:#fff}
    .platform-footer a:focus-visible{outline:3px solid #ffbf47;outline-offset:4px;border-radius:3px}
    .platform-footer-bottom{border-top:1px solid #514366;background:rgba(0,0,0,.14)}
    .platform-footer-bottom-inner{display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px 24px;width:100%;margin:auto;padding:18px 24px}
    .platform-footer-bottom p{margin:0;font-size:.85rem;color:#ddd4ef}
    @media(max-width:1100px){.platform-footer-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:640px){
        .platform-footer-grid{grid-template-columns:minmax(0,1fr);gap:26px;padding:32px 16px}
        .platform-footer-bottom-inner{flex-direction:column;padding:18px 16px}
        .filters input,.filters select{min-width:0;width:100%}
        .chat-form input{min-width:0}
        main input,main select,main textarea{font-size:1rem}
    }
    body>.container,.site-header>.nav,.portal-content>.main,.platform-footer-grid,.platform-footer-bottom-inner,.top{padding-inline:var(--page-gutter)}
    @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
