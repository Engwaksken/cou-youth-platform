<section class="panel accessibility-panel" id="accessPanel" role="dialog" aria-labelledby="accessTitle" aria-hidden="true" tabindex="-1">
    <div class="panel-head">
        <strong id="accessTitle">Accessibility settings</strong>
        <button class="btn btn-icon" type="button" data-close="accessPanel" aria-label="Close accessibility settings"><i class="fas fa-xmark" aria-hidden="true"></i></button>
    </div>
    <div class="panel-body">
        <p class="access-intro">Choose the adjustments that work for you. Settings are saved on this device when browser storage is available.</p>
        <fieldset>
            <legend>Text &amp; reading</legend>
            <div class="access-row"><span id="textSizeLabel">Text size</span><div class="switch" role="group" aria-labelledby="textSizeLabel"><button class="btn btn-icon" type="button" id="textDown" aria-label="Decrease text size">A−</button><output id="accessTextSize" aria-live="polite">100%</output><button class="btn btn-icon" type="button" id="textUp" aria-label="Increase text size">A+</button></div></div>
            <label class="access-option"><input id="dyslexia" type="checkbox"> Simple reading font</label>
            <label class="access-option"><input id="lineSpacing" type="checkbox"> Increased line spacing</label>
            <label class="access-option"><input id="letterSpacing" type="checkbox"> Increased letter &amp; word spacing</label>
            <label class="access-option"><input id="alignText" type="checkbox"> Left-aligned paragraphs</label>
        </fieldset>
        <fieldset>
            <legend>Vision &amp; navigation</legend>
            <label class="access-option"><input id="contrast" type="checkbox"> High contrast</label>
            <label class="access-option"><input id="grayscale" type="checkbox"> Grayscale content</label>
            <label class="access-option"><input id="highlightLinks" type="checkbox"> Highlight &amp; underline links</label>
            <label class="access-option"><input id="highlightHeadings" type="checkbox"> Highlight headings</label>
            <label class="access-option"><input id="largePointer" type="checkbox"> Larger mouse pointer</label>
            <label class="access-option"><input id="hideImages" type="checkbox"> Hide decorative images</label>
        </fieldset>
        <fieldset>
            <legend>Motion &amp; focus</legend>
            <label class="access-option"><input id="motion" type="checkbox"> Reduce animations &amp; motion</label>
            <label class="access-option"><input id="focusMode" type="checkbox"> Strong keyboard focus indicator</label>
        </fieldset>
        <fieldset>
            <legend>Read aloud</legend>
            <p class="access-help" id="speechHelp">Reads the page’s main text using your browser’s available voice. This is not a replacement for a screen reader.</p>
            <div class="access-speech-actions"><button class="btn" id="accessRead" type="button" aria-describedby="speechHelp">Read page</button><button class="btn" id="accessPause" type="button" disabled>Pause</button><button class="btn" id="accessStop" type="button" disabled>Stop</button></div>
        </fieldset>
        <details class="access-help"><summary>Keyboard &amp; assistive technology help</summary><p>Use Tab and Shift+Tab to move between controls, Enter or Space to activate them, and Escape to close this panel. Use the “Skip to main content” link at the start of the page to bypass navigation.</p><p>Your browser zoom, screen reader, voice control and device accessibility settings remain available. Captions and transcripts depend on the individual media; these settings do not generate them.</p></details>
        <p class="access-status" id="accessStatus" role="status"></p>
        <button class="btn" type="button" id="resetAccessibility">Reset all settings</button>
    </div>
</section>
