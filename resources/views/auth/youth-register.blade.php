@extends('public.layout')

@section('title', 'Create Account | COU Youth')

@section('content')
@php($brand = app(\App\Services\Branding\BrandingService::class)->data())

<section class="register-page" aria-labelledby="register-title">
    <div class="register-shell">
        <div class="register-brand">
            @if(!empty($brand['logo_url']))
                <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['short_name'] ?? 'COU Youth' }} logo" class="register-logo">
            @else
                <div class="register-logo-fallback" aria-hidden="true"><i class="fas fa-church"></i></div>
            @endif
            <strong>{{ $brand['short_name'] ?? 'COU Youth' }}</strong>
            <span>Faith · Community · Opportunity</span>
        </div>

        <div class="register-card">
            <div class="register-heading">
                <h1 id="register-title">Create Account</h1>
                <p>Join the Church of Uganda Youth Platform.</p>
            </div>

            @if($errors->any())
                <div class="register-alert" role="alert">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    <div>
                        <strong>Please correct the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="register-tabs" role="tablist" aria-label="Registration steps">
                <button type="button" class="register-tab is-active" id="account-tab" data-tab-target="account-panel" role="tab" aria-selected="true" aria-controls="account-panel">
                    <span>1</span> Account details
                </button>
                <button type="button" class="register-tab" id="guardian-tab" data-tab-target="guardian-panel" role="tab" aria-selected="false" aria-controls="guardian-panel">
                    <span>2</span> Guardian details
                </button>
            </div>

            <form method="post" action="{{ route('register.store') }}" id="registrationForm">
                @csrf

                <section class="register-panel is-active" id="account-panel" role="tabpanel" aria-labelledby="account-tab">
                    <div class="register-grid">
                        <div class="form-field full-width">
                            <label for="name">Full name</label>
                            <input id="name" class="field" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autocomplete="name">
                        </div>

                        <div class="form-field full-width">
                            <label for="email">Email address</label>
                            <input id="email" class="field" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autocomplete="email">
                        </div>

                        <div class="form-field">
                            <label for="date_of_birth">Date of birth</label>
                            <input id="date_of_birth" class="field" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required autocomplete="bday">
                        </div>

                        <div class="form-field">
                            <label for="school_institution">School / institution</label>
                            <input id="school_institution" class="field" name="school_institution" value="{{ old('school_institution') }}" placeholder="Enter school or institution">
                        </div>

                        <div class="form-field">
                            <label for="password">Password</label>
                            <input id="password" class="field" type="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                            <small>Use at least 8 characters with upper/lowercase letters and a number.</small>
                        </div>

                        <div class="form-field">
                            <label for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" class="field" type="password" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="register-actions end">
                        <button type="button" class="btn btn-primary" id="nextRegistrationStep">
                            Continue <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </section>

                <section class="register-panel" id="guardian-panel" role="tabpanel" aria-labelledby="guardian-tab" hidden>
                    <div class="guardian-intro">
                        <i class="fas fa-shield-heart" aria-hidden="true"></i>
                        <div>
                            <strong>Guardian details for ages 12–17</strong>
                            <p>These fields are optional for users aged 18 and above.</p>
                        </div>
                    </div>

                    <div class="register-grid">
                        <div class="form-field">
                            <label for="guardian_name">Guardian name</label>
                            <input id="guardian_name" class="field" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Enter guardian's full name">
                        </div>

                        <div class="form-field">
                            <label for="guardian_relationship">Relationship</label>
                            <input id="guardian_relationship" class="field" name="guardian_relationship" value="{{ old('guardian_relationship') }}" placeholder="e.g. Parent, guardian, relative">
                        </div>

                        <div class="form-field">
                            <label for="guardian_phone">Guardian phone</label>
                            <input id="guardian_phone" class="field" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="e.g. +256 7XX XXX XXX" autocomplete="tel">
                        </div>

                        <div class="form-field">
                            <label for="guardian_email">Guardian email</label>
                            <input id="guardian_email" class="field" type="email" name="guardian_email" value="{{ old('guardian_email') }}" placeholder="guardian@example.com" autocomplete="email">
                        </div>
                    </div>

                    <label class="consent-row">
                        <input type="checkbox" name="guardian_confirmed" value="1" @checked(old('guardian_confirmed'))>
                        <span>I confirm the guardian has consented to this youth account where required.</span>
                    </label>

                    <div class="register-actions split">
                        <button type="button" class="btn btn-outline" id="previousRegistrationStep">
                            <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> Create Account
                        </button>
                    </div>
                </section>
            </form>

            <div class="register-login-link">
                Already registered? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </div>
    </div>
</section>

<style>
    .register-page {
        min-height: calc(100vh - 150px);
        padding: 34px 18px 46px;
        background:
            radial-gradient(circle at 7% 12%, color-mix(in srgb, var(--primary) 7%, transparent) 0 100px, transparent 101px),
            radial-gradient(circle at 94% 88%, color-mix(in srgb, var(--secondary) 6%, transparent) 0 120px, transparent 121px),
            #f8f7fc;
    }

    .register-shell {
        width: min(100%, 700px);
        margin: 0 auto;
    }

    .register-brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 18px;
    }

    .register-logo,
    .register-logo-fallback {
        width: 76px;
        height: 76px;
        margin-bottom: 10px;
    }

    .register-logo {
        object-fit: contain;
    }

    .register-logo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: var(--primary);
        border: 1px solid #e4e7ec;
        border-radius: 10px;
        font-size: 30px;
    }

    .register-brand strong {
        color: #182230;
        font-size: 1.28rem;
    }

    .register-brand span {
        margin-top: 5px;
        color: #667085;
        font-size: .9rem;
    }

    .register-card {
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 10px;
        padding: 28px;
        box-shadow: 0 18px 50px rgba(16, 24, 40, .08);
    }

    .register-heading {
        text-align: center;
        margin-bottom: 22px;
    }

    .register-heading h1 {
        margin: 0;
        color: #182230;
        font-size: clamp(1.7rem, 4vw, 2rem);
    }

    .register-heading p {
        margin: 8px 0 0;
        color: #667085;
    }

    .register-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 18px;
        padding: 12px 14px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
    }

    .register-alert ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    .register-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 22px;
        padding: 5px;
        border-radius: 10px;
        background: #f2f4f7;
    }

    .register-tab {
        min-height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #667085;
        font: inherit;
        font-weight: 800;
        cursor: pointer;
    }

    .register-tab span {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #d0d5dd;
        font-size: .78rem;
    }

    .register-tab.is-active {
        background: var(--primary);
        color: #fff;
    }

    .register-tab.is-active span {
        color: var(--primary);
        border-color: #fff;
    }

    .register-panel[hidden] {
        display: none !important;
    }

    .register-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-field label {
        display: block;
        margin-bottom: 7px;
        color: #344054;
        font-size: .92rem;
        font-weight: 700;
    }

    .field {
        display: block;
        width: 100%;
        min-height: 50px;
        padding: 11px 13px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        background: #fff;
        color: #182230;
        font: inherit;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .field::placeholder {
        color: #98a2b3;
    }

    .field:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 13%, transparent);
    }

    .form-field small {
        display: block;
        margin-top: 6px;
        color: #667085;
        line-height: 1.4;
    }

    .guardian-intro {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 18px;
        padding: 13px 14px;
        border-radius: 10px;
        background: color-mix(in srgb, var(--primary) 7%, white);
        color: #344054;
    }

    .guardian-intro > i {
        margin-top: 3px;
        color: var(--primary);
    }

    .guardian-intro p {
        margin: 4px 0 0;
        color: #667085;
        font-size: .9rem;
    }

    .consent-row {
        display: flex;
        gap: 9px;
        align-items: flex-start;
        margin: 18px 0;
        color: #475467;
        line-height: 1.45;
    }

    .consent-row input {
        margin-top: 3px;
        accent-color: var(--primary);
    }

    .register-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
    }

    .register-actions.end {
        justify-content: flex-end;
    }

    .register-actions.split {
        justify-content: space-between;
    }

    .register-actions .btn {
        min-height: 48px;
        border-radius: 10px;
        justify-content: center;
    }

    .btn-outline {
        border: 1px solid var(--primary);
        background: #fff;
        color: var(--primary);
    }

    .btn-outline:hover,
    .btn-outline:focus-visible {
        background: var(--secondary);
        border-color: var(--secondary);
        color: #fff;
    }

    .register-login-link {
        margin-top: 22px;
        text-align: center;
        color: #667085;
        font-size: .92rem;
    }

    .register-login-link a {
        color: var(--primary);
        font-weight: 800;
        text-decoration: none;
    }

    .register-login-link a:hover,
    .register-login-link a:focus-visible {
        color: var(--secondary);
        text-decoration: underline;
    }

    @media (max-width: 650px) {
        .register-page {
            padding: 24px 14px 36px;
        }

        .register-card {
            padding: 22px 18px;
        }

        .register-grid {
            grid-template-columns: 1fr;
        }

        .full-width {
            grid-column: auto;
        }

        .register-tabs {
            grid-template-columns: 1fr;
        }

        .register-actions.split {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .register-actions.end {
            justify-content: stretch;
        }

        .register-actions .btn {
            width: 100%;
        }

        .register-logo,
        .register-logo-fallback {
            width: 66px;
            height: 66px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = Array.from(document.querySelectorAll('[data-tab-target]'));
        const panels = Array.from(document.querySelectorAll('.register-panel'));
        const nextButton = document.getElementById('nextRegistrationStep');
        const previousButton = document.getElementById('previousRegistrationStep');
        const accountPanel = document.getElementById('account-panel');

        function activatePanel(panelId) {
            tabs.forEach(function (tab) {
                const active = tab.getAttribute('data-tab-target') === panelId;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                const active = panel.id === panelId;
                panel.classList.toggle('is-active', active);
                panel.hidden = !active;
            });

            const activePanel = document.getElementById(panelId);
            const firstInput = activePanel ? activePanel.querySelector('input') : null;
            if (firstInput) firstInput.focus({preventScroll: true});
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activatePanel(tab.getAttribute('data-tab-target'));
            });
        });

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                const requiredInputs = Array.from(accountPanel.querySelectorAll('[required]'));
                const invalid = requiredInputs.find(function (input) { return !input.reportValidity(); });
                if (invalid) return;
                activatePanel('guardian-panel');
            });
        }

        if (previousButton) {
            previousButton.addEventListener('click', function () {
                activatePanel('account-panel');
            });
        }

        @if($errors->has('guardian_name') || $errors->has('guardian_relationship') || $errors->has('guardian_phone') || $errors->has('guardian_email') || $errors->has('guardian_confirmed'))
            activatePanel('guardian-panel');
        @endif
    });
</script>
@endsection
