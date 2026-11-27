import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

// The signup page's step script, minus the server-side Blade block that reopens
// a step after a failed submit (it is not JavaScript).
const page = readFileSync(new URL('../../resources/views/auth/youth-register.blade.php', import.meta.url), 'utf8');
const scripts = [...page.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(match => match[1]);
const source = scripts.find(script => script.includes('checkPasswordMatch'))
    .replace(/^\s*@if\b[\s\S]*?^\s*@endif\s*$/m, '');

function classList() {
    const classes = new Set();
    return {
        contains: name => classes.has(name),
        toggle: (name, force) => { (force ?? !classes.has(name)) ? classes.add(name) : classes.delete(name); },
    };
}

function input(id, { required = true } = {}) {
    const listeners = {};
    return {
        id, value: '', required, validationMessage: '', classList: classList(), reported: 0,
        setCustomValidity(message) { this.validationMessage = message; },
        reportValidity() { this.reported++; return this.validationMessage === '' && (!this.required || this.value !== ''); },
        addEventListener(type, fn) { listeners[type] = fn; },
        type(value) { this.value = value; listeners.input?.(); },
        focus() {},
    };
}

function signupPage() {
    const password = input('password');
    const confirm = input('password_confirmation');
    const name = input('name');
    const hint = { textContent: '', classList: classList() };
    const panel = (id, inputs) => ({
        id, hidden: id !== 'account-panel', classList: classList(),
        querySelectorAll: () => inputs.filter(field => field.required),
        querySelector: () => inputs[0] ?? null,
    });
    const panels = [panel('account-panel', [name, password, confirm]), panel('profile-panel', []), panel('guardian-panel', [])];
    const buttons = {};
    const button = id => (buttons[id] ??= { listeners: {}, addEventListener(type, fn) { this.listeners[type] = fn; } });
    const form = { listeners: {}, addEventListener(type, fn) { this.listeners[type] = fn; } };
    const byId = {
        password, password_confirmation: confirm, passwordMatchHint: hint, registrationForm: form,
        ...Object.fromEntries(panels.map(p => [p.id, p])),
    };
    let ready;
    const document = {
        addEventListener: (type, fn) => { if (type === 'DOMContentLoaded') ready = fn; },
        getElementById: id => byId[id] ?? button(id),
        querySelectorAll: selector => (selector === '.register-panel' ? panels : []),
    };
    runInNewContext(source, { document });
    ready();

    const visible = () => panels.find(p => !p.hidden).id;
    const click = id => buttons[id].listeners.click();
    const submit = () => { let prevented = false; form.listeners.submit({ preventDefault: () => { prevented = true; } }); return prevented; };

    return { name, password, confirm, hint, visible, click, submit };
}

test('shows whether the passwords match while typing', () => {
    const page = signupPage();
    page.password.type('StrongPass123');
    assert.equal(page.hint.textContent, '');

    page.confirm.type('StrongPass12');
    assert.equal(page.hint.textContent, "Passwords don't match.");
    assert.ok(page.hint.classList.contains('is-mismatch'));
    assert.ok(page.confirm.classList.contains('is-mismatch'));
    assert.equal(page.confirm.validationMessage, "Passwords don't match.");

    page.confirm.type('StrongPass123');
    assert.equal(page.hint.textContent, 'Passwords match.');
    assert.ok(page.hint.classList.contains('is-match'));
    assert.equal(page.confirm.validationMessage, '');
});

test('changing the first password re-checks the confirmation', () => {
    const page = signupPage();
    page.password.type('StrongPass123');
    page.confirm.type('StrongPass123');
    page.password.type('StrongPass999');
    assert.equal(page.hint.textContent, "Passwords don't match.");
});

test('Continue is blocked until the passwords match', () => {
    const page = signupPage();
    page.name.type('Youth Member');
    page.password.type('StrongPass123');
    page.confirm.type('Different123');

    page.click('nextToProfile');
    assert.equal(page.visible(), 'account-panel');
    assert.ok(page.confirm.reported > 0, 'the mismatch is reported to the user');

    page.confirm.type('StrongPass123');
    page.click('nextToProfile');
    assert.equal(page.visible(), 'profile-panel');
});

test('submitting with mismatched passwords is stopped and returns to the account step', () => {
    const page = signupPage();
    page.name.type('Youth Member');
    page.password.type('StrongPass123');
    page.confirm.type('StrongPass123');
    page.click('nextToProfile');
    page.click('nextToGuardian');
    assert.equal(page.visible(), 'guardian-panel');

    page.confirm.value = 'Changed';  // e.g. autofill without an input event
    assert.equal(page.submit(), true, 'submit is prevented');
    assert.equal(page.visible(), 'account-panel');

    page.confirm.type('StrongPass123');
    assert.equal(page.submit(), false, 'matching passwords submit normally');
});
