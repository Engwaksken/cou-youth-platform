import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/views/partials/accessibility-enhancements.blade.php', import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];

function widget({ saved = '{}', blocked = false } = {}) {
    const nodes = new Map();
    const listeners = {};
    let focused;
    let stored = saved;
    let textScale;
    const node = id => {
        if (!nodes.has(id)) {
            const classes = new Set();
            const attributes = {};
            nodes.set(id, {
                id, isConnected: true, checked: false, disabled: false, textContent: '',
                classList: {
                    add: name => classes.add(name), remove: name => classes.delete(name),
                    contains: name => classes.has(name),
                    toggle(name, on) { if (on) classes.add(name); else classes.delete(name); },
                },
                setAttribute: (key, value) => { attributes[key] = value; },
                getAttribute: key => attributes[key],
                focus: () => { focused = id; },
                querySelector: () => null,
            });
        }
        return nodes.get(id);
    };
    const context = {
        document: {
            body: node('body'), documentElement: { lang: 'en', style: { setProperty: (_, value) => { textScale = value; } } },
            getElementById: node,
            querySelectorAll: () => [],
            querySelector: () => ['accessPanel', 'chatPanel'].map(node).find(item => item.classList.contains('open')),
            addEventListener: (name, handler) => { listeners[name] = handler; },
        },
        localStorage: {
            getItem() { if (blocked) throw Error('blocked'); return stored; },
            setItem(_, value) { if (blocked) throw Error('blocked'); stored = value; },
        },
        window: { addEventListener() {} },
    };
    runInNewContext(source, context);
    return { node, listeners, focused: () => focused, stored: () => JSON.parse(stored), scale: () => textScale };
}

test('text sizing is bounded at 100–200% and saved', () => {
    const app = widget();
    assert.equal(app.scale(), 1);
    for (let index = 0; index < 15; index++) app.node('textUp').onclick();
    assert.equal(app.scale(), 2);
    assert.equal(app.node('textUp').disabled, true);
    assert.equal(app.node('accessTextSize').textContent, '200%');
    assert.equal(app.stored().scale, 2);
    for (let index = 0; index < 15; index++) app.node('textDown').onclick();
    assert.equal(app.scale(), 1);
    assert.equal(app.node('textDown').disabled, true);
});

test('preferences restore and reset with visible controls synchronized', () => {
    const app = widget({ saved: '{"lineSpacing":true,"highlightLinks":true,"scale":1.5}' });
    assert.equal(app.node('lineSpacing').checked, true);
    assert.equal(app.node('body').classList.contains('access-line-spacing'), true);
    app.node('focusMode').onchange({ target: { checked: true } });
    assert.equal(app.node('body').classList.contains('access-focus-mode'), true);
    app.node('resetAccessibility').onclick();
    assert.equal(app.node('highlightLinks').checked, false);
    assert.equal(app.node('body').classList.contains('access-focus-mode'), false);
    assert.equal(app.scale(), 1);
});

test('panel opens with focus and Escape restores trigger focus', () => {
    const app = widget();
    app.node('accessToggle').onclick();
    assert.equal(app.node('accessToggle').getAttribute('aria-expanded'), 'true');
    assert.equal(app.node('accessPanel').getAttribute('aria-hidden'), 'false');
    assert.equal(app.focused(), 'accessPanel');
    let prevented = false;
    app.listeners.keydown({ key: 'Escape', preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(app.node('accessPanel').getAttribute('aria-hidden'), 'true');
    assert.equal(app.focused(), 'accessToggle');
});

test('opening chat closes accessibility and updates both triggers', () => {
    const app = widget();
    app.node('accessToggle').onclick();
    app.node('chatToggle').onclick();
    assert.equal(app.node('accessPanel').getAttribute('aria-hidden'), 'true');
    assert.equal(app.node('accessToggle').getAttribute('aria-expanded'), 'false');
    assert.equal(app.node('chatToggle').getAttribute('aria-expanded'), 'true');
});

test('corrupt or blocked storage does not disable controls', () => {
    for (const options of [{ saved: 'broken' }, { saved: 'null' }, { blocked: true }]) {
        const app = widget(options);
        app.node('textUp').onclick();
        assert.equal(app.scale(), 1.1);
        app.node('accessToggle').onclick();
        assert.equal(app.node('accessPanel').classList.contains('open'), true);
    }
});

test('unsupported read aloud is disabled with an explanation', () => {
    const app = widget();
    assert.equal(app.node('accessRead').disabled, true);
    assert.match(app.node('speechHelp').textContent, /not supported/);
});
