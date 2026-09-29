@php
    try {
        $brand = app(\App\Services\Branding\BrandingService::class)->data();
    } catch (\Throwable $e) {
        $brand = [
            'short_name' => 'COU Youth Platform',
            'primary_color' => '#4B2E83',
            'secondary_color' => '#204F78',
            'logo_url' => null,
        ];
    }

    $dashboardUrl = auth()->check() && Route::has('youth.dashboard')
        ? route('youth.dashboard')
        : route('home');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Access unavailable | {{ $brand['short_name'] ?? 'COU Youth Platform' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root{
            --primary:{{ $brand['primary_color'] ?? '#4B2E83' }};
            --secondary:{{ $brand['secondary_color'] ?? '#204F78' }};
            --primary-soft:color-mix(in srgb,var(--primary) 9%,#fff 91%);
            --text:#182230;
            --muted:#667085;
            --border:#e7e9f0;
            --bg:#f6f7fb;
        }
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;background:linear-gradient(180deg,#f8f9fd 0,#f4f6fb 100%);font-family:'DM Sans',Arial,sans-serif;color:var(--text);display:grid;place-items:center;padding:24px}
        .error-shell{width:min(680px,100%)}
        .brand-row{display:flex;align-items:center;justify-content:center;gap:11px;margin-bottom:18px;color:var(--primary);font-weight:800}
        .brand-logo{width:48px;height:48px;border-radius:13px;border:1px solid var(--border);background:#fff;display:grid;place-items:center;overflow:hidden;box-shadow:0 6px 20px rgba(16,24,40,.07)}
        .brand-logo img{width:100%;height:100%;object-fit:contain;padding:4px}
        .brand-logo i{font-size:21px}
        .error-card{background:#fff;border:1px solid var(--border);border-radius:20px;padding:38px;box-shadow:0 20px 60px rgba(15,23,42,.08);text-align:center}
        .error-icon{width:72px;height:72px;border-radius:18px;margin:0 auto 18px;display:grid;place-items:center;background:var(--primary-soft);color:var(--primary);font-size:28px}
        .eyebrow{display:inline-block;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);margin-bottom:8px}
        h1{margin:0;font-size:clamp(1.8rem,5vw,2.45rem);letter-spacing:-.025em}
        p{max-width:530px;margin:12px auto 0;color:var(--muted);line-height:1.7}
        .help{margin-top:18px;padding:13px 15px;border:1px solid var(--border);border-radius:12px;background:#fafbfc;color:#475467;font-size:.92rem}
        .actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:24px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700;border:1px solid var(--border);background:#fff;color:#344054;transition:.18s ease}
        .btn:hover{transform:translateY(-1px);border-color:color-mix(in srgb,var(--primary) 30%,var(--border))}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-primary:hover{background:var(--secondary);border-color:var(--secondary)}
        @media(max-width:520px){body{padding:16px}.error-card{padding:28px 18px}.actions{flex-direction:column}.btn{width:100%}}
    </style>
</head>
<body>
<main class="error-shell">
    <div class="brand-row">
        <span class="brand-logo">
            @if(!empty($brand['logo_url']))
                <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['short_name'] ?? 'COU Youth Platform' }} logo">
            @else
                <i class="fas fa-church" aria-hidden="true"></i>
            @endif
        </span>
        <span>{{ $brand['short_name'] ?? 'COU Youth Platform' }}</span>
    </div>

    <section class="error-card">
        <div class="error-icon"><i class="fas fa-lock" aria-hidden="true"></i></div>
        <span class="eyebrow">Access unavailable</span>
        <h1>This page is not available for your account</h1>
        <p>The page you tried to open is restricted to authorised users. You can continue using the areas of the platform available to your account.</p>
        <div class="help"><i class="fas fa-circle-info" aria-hidden="true"></i> If you believe you should have access, please contact your platform administrator.</div>
        <div class="actions">
            <a class="btn" href="javascript:history.back()"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go back</a>
            <a class="btn btn-primary" href="{{ $dashboardUrl }}"><i class="fas fa-house" aria-hidden="true"></i> {{ auth()->check() ? 'Go to dashboard' : 'Go to website' }}</a>
        </div>
    </section>
</main>
</body>
</html>
