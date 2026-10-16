<style>
    /*
     * Shared viewport scale for every layout: phones, tablets, laptops, 27" QHD monitors and TVs.
     * Sizes are rem-based, so raising --base-font on large screens grows type, controls and
     * content width together instead of stretching lines across the screen.
     */
    :root{--base-font:16px;--page-gutter:24px;--content-max:80rem;--portal-max:110rem;--top-h:4.25rem;--side-w:16.875rem}
    @media(max-width:640px){:root{--page-gutter:16px}}
    @media(min-width:801px) and (max-width:1100px){:root{--side-w:15rem}}
    @media(min-width:1200px){:root{--page-gutter:32px}}
    @media(min-width:1600px){:root{--content-max:88rem}}
    @media(min-width:1920px){:root{--base-font:17px;--page-gutter:3rem;--content-max:90rem}}
    @media(min-width:2400px){:root{--base-font:20px;--content-max:96rem}}
    @media(min-width:3200px){:root{--base-font:26px}}
    html{font-size:calc(var(--base-font) * var(--text-scale,1));-webkit-text-size-adjust:100%;text-size-adjust:100%}
</style>
