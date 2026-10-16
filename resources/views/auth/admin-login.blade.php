<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Church of Uganda Youth Platform</title>
    @php
        try {
            $adminLogo = \App\Models\SiteSetting::get('logo');
            $adminFavicon = \App\Models\SiteSetting::get('favicon');
        } catch (\Throwable $e) {
            $adminLogo = null;
            $adminFavicon = null;
        }
    @endphp
    @if(!empty($adminFavicon))
        <link rel="icon" href="{{ asset('storage/'.$adminFavicon) }}">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        *{box-sizing:border-box}
        :root{--primary:#4b2e83;--secondary:#204f78;--text:#182230;--muted:#667085;--border:#d0d5dd;--surface:#fff;--page:#f8f7fc}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:radial-gradient(circle at 10% 12%,rgba(75,46,131,.08) 0 90px,transparent 91px),radial-gradient(circle at 90% 88%,rgba(32,79,120,.07) 0 120px,transparent 121px),var(--page);font-family:Arial,sans-serif;color:var(--text)}
        .login-shell{width:100%;max-width:26.875rem}
        .login-card{width:100%;background:var(--surface);padding:30px;border:1px solid #e6e7ec;border-radius:10px;box-shadow:0 18px 50px rgba(16,24,40,.08)}
        .brand{text-align:center;margin-bottom:22px}
        .brand-logo{width:68px;height:68px;max-width:68px;max-height:68px;object-fit:contain;display:block;margin:0 auto 10px}
        .brand-fallback{width:68px;height:68px;margin:0 auto 10px;border:1px solid #e4e7ec;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.75rem;font-weight:800}
        .brand strong{display:block;font-size:1.3125rem;line-height:1.2}
        .brand span{display:block;margin-top:5px;color:var(--muted);font-size:0.875rem}
        .heading{text-align:center;margin-bottom:22px}
        h1{margin:0;font-size:1.75rem;line-height:1.15}
        .subtitle{color:var(--muted);margin:8px auto 0;line-height:1.55;max-width:21.25rem;text-align:center}
        .form-group{margin-bottom:17px}
        label{display:block;margin-bottom:7px;font-weight:700;color:#344054;font-size:0.875rem}
        input[type=email],input[type=password]{width:100%;min-height:50px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:0.9375rem;color:var(--text);outline:none;transition:border-color .2s ease,box-shadow .2s ease}
        input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(75,46,131,.12)}
        input::placeholder{color:#98a2b3}
        .pw-wrap{display:block;position:relative}
        .pw-wrap input{padding-right:48px}
        .pw-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:36px;height:36px;border:0;background:transparent;color:#667085;border-radius:8px;cursor:pointer;font-size:1.0625rem;display:inline-flex;align-items:center;justify-content:center}
        .pw-toggle:hover,.pw-toggle:focus-visible{background:#f2f4f7;color:var(--primary);outline:none}
        .remember{display:flex;gap:8px;align-items:center;margin-bottom:20px;color:#475467;font-size:0.875rem}
        .remember input{accent-color:var(--primary)}
        .submit-btn{width:100%;border:0;background:var(--primary);color:#fff;padding:13px;border-radius:10px;font-size:1rem;font-weight:800;cursor:pointer;transition:background .2s ease}
        .submit-btn:hover,.submit-btn:focus-visible{background:var(--secondary);outline:none}
        .error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:10px 12px;margin-bottom:16px}
        [data-auth-flash]{transition:opacity .5s ease}[data-auth-flash].is-fading{opacity:0}
        @media(max-width:520px){body{align-items:flex-start;padding:24px 14px}.login-card{padding:24px 18px}.brand-logo,.brand-fallback{width:62px;height:62px;max-width:62px;max-height:62px}}
    </style>
    @include('partials.form-control-styles')
@include('partials.responsive-scale')
</head>
<body>
    <main class="login-shell">
        <section class="login-card" aria-labelledby="admin-login-title">
            <div class="brand">
                @if(!empty($adminLogo))
                    <img class="brand-logo" src="{{ asset('storage/'.$adminLogo) }}" alt="Church of Uganda Youth Platform logo">
                @else
                    <div class="brand-fallback" aria-hidden="true"><i class="fas fa-church"></i></div>
                @endif
                <strong>COU Youth Platform</strong>
                <span>Administrator access</span>
            </div>

            <div class="heading">
                <h1 id="admin-login-title">Login</h1>
                <div class="subtitle">For authorised Church of Uganda Youth Platform administrators.</div>
            </div>

            @if ($errors->any())
                <div class="error" data-auth-flash role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <span class="pw-wrap">
                        <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" data-pw-toggle="password" aria-label="Show password" aria-pressed="false">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>

                <button type="submit" class="submit-btn"><i class="fas fa-right-to-bracket" aria-hidden="true"></i> Sign In</button>
            </form>
        </section>
    </main>

    <script>
        (function(){
            document.querySelectorAll('[data-pw-toggle]').forEach(function(button){
                var input=document.getElementById(button.getAttribute('data-pw-toggle'));
                var icon=button.querySelector('i');
                if(!input)return;
                button.addEventListener('click',function(){
                    var show=input.type==='password';
                    input.type=show?'text':'password';
                    button.setAttribute('aria-pressed',String(show));
                    button.setAttribute('aria-label',show?'Hide password':'Show password');
                    if(icon){
                        icon.classList.toggle('fa-eye',!show);
                        icon.classList.toggle('fa-eye-slash',show);
                    }
                });
            });
            document.querySelectorAll('[data-auth-flash]').forEach(function(el){
                setTimeout(function(){el.classList.add('is-fading');setTimeout(function(){el.remove()},550)},5000);
            });
        })();
    </script>
</body>
</html>
