@extends('public.layout')

@section('title', 'Login | COU Youth')

@section('content')
@php($brand = app(\App\Services\Branding\BrandingService::class)->data())

<section class="auth-page" aria-labelledby="youth-login-title">
    <div class="auth-shell">
        <section class="auth-card">
            <div class="auth-brand-panel" aria-label="COU Youth">
                @if(!empty($brand['logo_url']))
                    <img
                        src="{{ $brand['logo_url'] }}"
                        alt="{{ $brand['short_name'] ?? 'COU Youth' }} logo"
                        class="auth-logo"
                    >
                @else
                    <div class="auth-logo-fallback" aria-hidden="true">
                        <i class="fas fa-church"></i>
                    </div>
                @endif

                <strong>{{ $brand['short_name'] ?? 'COU Youth' }}</strong>
                <span>Faith · Community · Opportunity</span>
            </div>

            <div class="auth-heading">
                <h1 id="youth-login-title">Login</h1>
                <p>For Church of Uganda Youth Platform youth members.</p>
            </div>

            @if($errors->any())
                <div class="auth-alert" role="alert">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="post" action="{{ route('login.attempt') }}" class="auth-form" novalidate>
                @csrf

                <div class="auth-field">
                    <label for="email">Email address</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            placeholder="Enter your email address"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <div class="auth-label-row">
                        <label for="password">Password</label>
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>

                    <div class="auth-input-wrap">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            required
                        >
                        <button
                            type="button"
                            class="password-toggle"
                            id="passwordToggle"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in</span>
                </label>

                <button type="submit" class="btn btn-primary auth-submit">
                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="auth-divider" aria-hidden="true">
                <span>New to COU Youth?</span>
            </div>

            <a href="{{ route('register') }}" class="auth-secondary-action">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                <span>Create youth account</span>
            </a>
        </section>
    </div>
</section>

<style>
    .auth-page {
        min-height: calc(100vh - 160px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 42px 20px;
        background:
            radial-gradient(circle at 8% 12%, color-mix(in srgb, var(--primary) 8%, transparent) 0 96px, transparent 97px),
            radial-gradient(circle at 92% 82%, color-mix(in srgb, var(--secondary) 7%, transparent) 0 120px, transparent 121px),
            #f8f7fc;
    }

    .auth-shell { width: min(100%, 460px); }

    .auth-card {
        background: #fff;
        border: 1px solid #e6e7ec;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 18px 50px rgba(16, 24, 40, .08);
    }

    .auth-brand-panel {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-bottom: 22px;
    }

    .auth-logo,
    .auth-logo-fallback {
        width: 68px;
        height: 68px;
        margin-bottom: 10px;
    }

    .auth-logo { object-fit: contain; display: block; }

    .auth-logo-fallback {
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: var(--primary);
        border: 1px solid color-mix(in srgb, var(--primary) 15%, #e5e7eb);
        font-size: 28px;
    }

    .auth-brand-panel strong {
        color: #182230;
        font-size: 1.25rem;
        line-height: 1.2;
    }

    .auth-brand-panel span {
        margin-top: 5px;
        color: #667085;
        font-size: .9rem;
    }

    .auth-heading {
        margin-bottom: 22px;
        text-align: center;
    }

    .auth-heading h1 {
        margin: 0;
        color: #182230;
        font-size: clamp(1.7rem, 4vw, 2rem);
        line-height: 1.15;
    }

    .auth-heading p {
        margin: 8px auto 0;
        max-width: 360px;
        color: #667085;
        line-height: 1.55;
    }

    .auth-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 18px;
        padding: 12px 14px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
        font-size: .92rem;
    }

    .auth-form { display: grid; gap: 16px; }

    .auth-field label,
    .auth-label-row label {
        color: #344054;
        font-weight: 700;
        font-size: .92rem;
    }

    .auth-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
    }

    .auth-label-row a {
        color: var(--primary);
        font-weight: 700;
        font-size: .88rem;
        text-decoration: none;
    }

    .auth-label-row a:hover,
    .auth-label-row a:focus-visible {
        color: var(--secondary);
        text-decoration: underline;
    }

    .auth-field > label { display: block; margin-bottom: 7px; }

    .auth-input-wrap { position: relative; display: flex; align-items: center; }

    .auth-input-wrap > i {
        position: absolute;
        left: 15px;
        color: var(--primary);
        pointer-events: none;
    }

    .auth-input-wrap input {
        width: 100%;
        min-height: 52px;
        padding: 12px 48px 12px 44px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        background: #fff;
        color: #182230;
        font: inherit;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .auth-input-wrap input::placeholder { color: #98a2b3; }

    .auth-input-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 14%, transparent);
    }

    .password-toggle {
        position: absolute;
        right: 8px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #667085;
        cursor: pointer;
    }

    .password-toggle:hover,
    .password-toggle:focus-visible {
        background: #f2f4f7;
        color: var(--primary);
        outline: none;
    }

    .remember-row {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #475467;
        font-size: .92rem;
        width: fit-content;
    }

    .remember-row input {
        width: 17px;
        height: 17px;
        accent-color: var(--primary);
    }

    .auth-submit {
        width: 100%;
        min-height: 50px;
        justify-content: center;
        border-radius: 10px;
        font-weight: 800;
    }

    .auth-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 22px 0 14px;
        color: #98a2b3;
        font-size: .82rem;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        height: 1px;
        flex: 1;
        background: #eaecf0;
    }

    .auth-secondary-action {
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid var(--primary);
        border-radius: 10px;
        color: var(--primary);
        background: #fff;
        font-weight: 800;
        text-decoration: none;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .auth-secondary-action:hover,
    .auth-secondary-action:focus-visible {
        background: var(--secondary) !important;
        border-color: var(--secondary) !important;
        color: #fff !important;
        outline: none;
    }

    @media (max-width: 560px) {
        .auth-page { align-items: flex-start; padding: 24px 14px 36px; }
        .auth-card { padding: 24px 18px; }
        .auth-logo,
        .auth-logo-fallback { width: 62px; height: 62px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-input-wrap input,
        .auth-secondary-action { transition: none; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('passwordToggle');
        const input = document.getElementById('password');

        if (!button || !input) return;

        button.addEventListener('click', function () {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-pressed', showing ? 'false' : 'true');
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    });
</script>
@endsection
