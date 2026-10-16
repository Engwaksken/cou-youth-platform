<style>
    .accessibility-panel{width:min(440px,calc(100vw - 32px));max-height:calc(100vh - 110px);max-height:calc(100dvh - 110px)}
    .accessibility-panel .panel-head{flex-shrink:0;flex-wrap:wrap}
    .accessibility-panel .panel-body{min-height:0;overscroll-behavior:contain}
    .accessibility-panel fieldset{min-width:0;margin:0 0 16px;padding:10px 12px;border:1px solid var(--border);border-radius:8px}
    .accessibility-panel legend{font-weight:800;padding:0 5px}
    .access-intro,.access-help{font-size:.9rem;line-height:1.6;margin:0 0 14px}
    .access-option{display:flex;align-items:center;gap:12px;min-height:44px;padding:8px 0;cursor:pointer;overflow-wrap:anywhere}
    .access-option input{width:20px;height:20px;flex:0 0 20px;accent-color:var(--primary)}
    .accessibility-panel .access-row{flex-wrap:wrap}
    .accessibility-panel .switch{align-items:center;flex-wrap:wrap}
    .accessibility-panel output{min-width:48px;text-align:center}
    .access-speech-actions{display:flex;flex-wrap:wrap;gap:8px}
    .accessibility-panel button:disabled{opacity:.5;cursor:not-allowed;transform:none}
    .accessibility-panel summary{cursor:pointer;min-height:44px;font-weight:700}
    .access-status{font-size:.85rem;line-height:1.5}
    body.grayscale{filter:none}
    body.grayscale main,body.grayscale .site-header,body.grayscale .platform-footer{filter:grayscale(1)}
    body.access-line-spacing main p,body.access-line-spacing main li{line-height:2!important}
    body.access-letter-spacing main{letter-spacing:.08em;word-spacing:.16em}
    body.access-align-text main p{text-align:left!important}
    body.access-highlight-links a,body.access-highlight-links a:hover,body.access-highlight-links a:focus{outline:2px solid currentColor;outline-offset:2px;text-decoration:underline!important;text-underline-offset:4px}
    body.access-highlight-headings main :is(h1,h2,h3,h4,h5,h6){border-inline-start:4px solid currentColor;padding-inline-start:10px}
    body.access-hide-images main img[alt=""]{visibility:hidden}
    body.access-large-pointer,body.access-large-pointer *{cursor:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='48' viewBox='0 0 40 48'%3E%3Cpath d='M3 2v36l9-9 8 16 8-4-8-16h14Z' fill='black' stroke='white' stroke-width='3'/%3E%3C/svg%3E") 3 2,auto}
    body.access-focus-mode :focus-visible{outline:4px solid #111!important;outline-offset:4px!important;box-shadow:0 0 0 8px #ffdf00!important}
    body.high-contrast{--bg:#fff;--surface:#fff;--surface-soft:#fff;--text:#000;--muted:#111;--border:#000;--primary:#24105c;--primary-2:#123451}
    body.high-contrast main :is(p,h1,h2,h3,h4,h5,h6,label,li){color:#000!important}
    body.high-contrast main :is(.card,.giving-message,.explore-card){background:#fff!important;color:#000!important;border:2px solid #000}
    @media(max-width:640px),(max-height:500px){.accessibility-panel{right:8px;bottom:8px;width:calc(100vw - 16px);max-height:calc(100vh - 16px);max-height:calc(100dvh - 16px)}.accessibility-panel .panel-body{padding:12px}.accessibility-panel fieldset{padding:8px}.accessibility-panel .panel-head strong{min-width:0;overflow-wrap:anywhere}}
</style>
<script>
(() => {
    const panel = document.getElementById('accessPanel');
    const toggle = document.getElementById('accessToggle');
    const status = document.getElementById('accessStatus');
    const flags = {contrast:'high-contrast',grayscale:'grayscale',dyslexia:'dyslexia',motion:'reduce-motion',lineSpacing:'access-line-spacing',letterSpacing:'access-letter-spacing',alignText:'access-align-text',highlightLinks:'access-highlight-links',highlightHeadings:'access-highlight-headings',largePointer:'access-large-pointer',hideImages:'access-hide-images',focusMode:'access-focus-mode'};
    let state = {};
    try { const saved = JSON.parse(localStorage.getItem('cou-accessibility') || '{}'); if (saved && typeof saved === 'object' && !Array.isArray(saved)) state = saved; } catch (_) {}
    const scale = () => Math.max(1, Math.min(2, Number(state.scale) || 1));
    const apply = () => {
        state.scale = scale();
        document.documentElement.style.setProperty('--text-scale', state.scale);
        document.getElementById('accessTextSize').textContent = Math.round(state.scale * 100) + '%';
        document.getElementById('textDown').disabled = state.scale <= 1;
        document.getElementById('textUp').disabled = state.scale >= 2;
        Object.entries(flags).forEach(([id, className]) => {
            const checked = state[id] === true;
            document.body.classList.toggle(className, checked);
            document.getElementById(id).checked = checked;
        });
        try { localStorage.setItem('cou-accessibility', JSON.stringify(state)); } catch (_) { status.textContent = 'Settings apply now, but this browser cannot save them.'; }
    };
    Object.keys(flags).forEach(id => { document.getElementById(id).onchange = event => { state[id] = event.target.checked; apply(); }; });
    document.getElementById('textUp').onclick = () => { state.scale = Math.round((scale() + .1) * 10) / 10; apply(); };
    document.getElementById('textDown').onclick = () => { state.scale = Math.round((scale() - .1) * 10) / 10; apply(); };
    let returnFocus = toggle;
    const close = (element, restore = true) => {
        element.classList.remove('open'); element.setAttribute('aria-hidden', 'true');
        document.getElementById(element.id === 'accessPanel' ? 'accessToggle' : 'chatToggle').setAttribute('aria-expanded', 'false');
        if (restore) (returnFocus?.isConnected ? returnFocus : toggle).focus();
    };
    const show = (element, trigger) => {
        if (element.classList.contains('open')) { close(element); return; }
        ['accessPanel','chatPanel'].forEach(id => close(document.getElementById(id), false));
        returnFocus = trigger; element.classList.add('open'); element.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        (element.querySelector('[data-close]') || element).focus();
    };
    toggle.setAttribute('aria-controls', panel.id); toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-haspopup', 'dialog');
    toggle.onclick = () => show(panel, toggle);
    const chatToggle = document.getElementById('chatToggle');
    chatToggle.setAttribute('aria-controls','chatPanel'); chatToggle.setAttribute('aria-expanded','false');
    chatToggle.onclick = () => show(document.getElementById('chatPanel'), chatToggle);
    document.querySelectorAll('[data-chat]').forEach(button => {
        button.onclick = () => {
            const chat = document.getElementById('chatPanel');
            if (!chat.classList.contains('open')) show(chat, chatToggle);
            const input = document.getElementById('chatInput');
            if (input) { input.value = button.dataset.chat; input.focus(); }
        };
    });
    document.querySelectorAll('[data-close]').forEach(button => { button.onclick = () => close(document.getElementById(button.dataset.close)); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') { const open = document.querySelector('.panel.open'); if (open) { event.preventDefault(); close(open); } }
    });
    const read = document.getElementById('accessRead');
    const pause = document.getElementById('accessPause');
    const stop = document.getElementById('accessStop');
    const speechSupported = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;
    let utterances = [];
    const resetSpeech = () => { read.disabled = !speechSupported; pause.disabled = true; stop.disabled = true; pause.textContent = 'Pause'; utterances = []; };
    const stopSpeech = () => { if (speechSupported) window.speechSynthesis.cancel(); resetSpeech(); };
    if (!speechSupported) { read.disabled = true; document.getElementById('speechHelp').textContent = 'Read aloud is not supported by this browser. Use your device’s screen reader or text-to-speech tools.'; }
    read.onclick = () => {
        stopSpeech();
        const text = document.getElementById('main-content')?.innerText.trim();
        if (!text) { status.textContent = 'There is no main page text to read.'; return; }
        const chunks = text.match(/[\s\S]{1,180}[.!?\s]|[\s\S]{1,180}/g) || [text];
        utterances = chunks.map(chunk => { const utterance = new SpeechSynthesisUtterance(chunk); utterance.lang = document.documentElement.lang || 'en'; return utterance; });
        utterances[utterances.length - 1].onend = () => { resetSpeech(); status.textContent = 'Finished reading.'; };
        utterances.forEach(utterance => { utterance.onerror = () => { stopSpeech(); status.textContent = 'Reading stopped. Your browser or voice may be unavailable.'; }; });
        read.disabled = true; pause.disabled = false; stop.disabled = false;
        utterances.forEach(utterance => window.speechSynthesis.speak(utterance));
    };
    pause.onclick = () => { if (window.speechSynthesis.paused) { window.speechSynthesis.resume(); pause.textContent = 'Pause'; } else { window.speechSynthesis.pause(); pause.textContent = 'Resume'; } };
    stop.onclick = stopSpeech;
    window.addEventListener('pagehide', stopSpeech);
    document.getElementById('resetAccessibility').onclick = () => { stopSpeech(); state = {}; status.textContent = 'All accessibility settings reset.'; apply(); };
    apply();
})();
</script>
