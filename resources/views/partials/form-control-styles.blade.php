<style>
    :root{--control-height:40px;--control-font-size:.875rem}
    /* Only single-line text controls: preserve native checkboxes, files and sliders. */
    body input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]):not([type="range"]):not([type="color"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="image"]),
    body select:not([multiple]):not([size]){
        box-sizing:border-box;height:var(--control-height)!important;min-height:var(--control-height)!important;
        padding-block:8px!important;font-size:var(--control-font-size)!important;line-height:1.4;border-radius:8px;
    }
    body textarea{box-sizing:border-box;min-height:104px;padding:10px 12px;font-size:var(--control-font-size);line-height:1.5;border-radius:8px;resize:vertical}
    body .btn,body .submit-btn,body .auth-secondary-action,body .register-tab,body input[type="submit"],body input[type="button"],body input[type="reset"]{
        box-sizing:border-box;min-height:var(--control-height)!important;height:auto;
        padding:8px 14px;font-size:var(--control-font-size);line-height:1.4;border-radius:8px;font-weight:600;
    }
    body .btn-icon{width:var(--control-height);height:var(--control-height);padding:0}
    body .btn.btn-primary:hover,body .btn.btn-primary:focus-visible{
        background:var(--secondary,var(--primary-2,#204f78));
        border-color:var(--secondary,var(--primary-2,#204f78));color:#fff;
    }
    @media(max-width:640px),(pointer:coarse){:root{--control-height:44px;--control-font-size:1rem}}
</style>
