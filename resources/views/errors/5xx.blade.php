@php
    $status = method_exists($exception ?? null, 'getStatusCode') ? $exception->getStatusCode() : 500;
    $title = $status === 503 ? 'Service Temporarily Unavailable' : 'Something Went Wrong';
    $message = $status === 503
        ? 'The platform is temporarily unavailable while we complete maintenance or recover a service. Please try again shortly.'
        : 'The platform could not complete your request. The issue has been recorded. Please try again or return to the website.';
    try {
        $brand = app(\App\Services\Branding\BrandingService::class)->data();
    } catch (\Throwable $e) {
        $brand = ['short_name' => 'COU Youth Platform', 'primary_color' => '#4B2E83', 'secondary_color' => '#204F78', 'logo_url' => null];
    }
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $status }} - {{ $title }}</title>
<style>
:root{--primary:{{ $brand['primary_color'] ?? '#4B2E83' }};--secondary:{{ $brand['secondary_color'] ?? '#204F78' }};--text:#1f2937;--muted:#667085;--border:#e5e7eb;--bg:#f6f7fb}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:var(--bg);font-family:Arial,Helvetica,sans-serif;color:var(--text)}.error-card{width:min(620px,100%);background:#fff;border:1px solid var(--border);border-radius:22px;padding:34px;box-shadow:0 20px 60px rgba(15,23,42,.08);text-align:center}.logo{width:72px;height:72px;margin:0 auto 16px;border:1px solid var(--border);border-radius:18px;display:grid;place-items:center;overflow:hidden;background:#fff}.logo img{width:100%;height:100%;object-fit:contain;padding:5px}.fallback{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;font-weight:900;font-size:24px}.code{font-size:clamp(3rem,12vw,6rem);line-height:1;font-weight:900;color:var(--primary);margin:8px 0}.error-card h1{margin:8px 0;font-size:clamp(1.5rem,5vw,2.2rem)}.error-card p{margin:10px auto 24px;max-width:500px;color:var(--muted);line-height:1.65}.actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;border-radius:11px;text-decoration:none;font-weight:800;border:1px solid var(--border);color:var(--text);background:#fff}.btn-primary{background:linear-gradient(135deg,var(--primary),var(--secondary));border-color:transparent;color:#fff}@media(max-width:520px){.error-card{padding:24px 18px}.actions{flex-direction:column}.btn{width:100%}}
</style>
</head>
<body>
<main class="error-card">
    @if(!empty($brand['logo_url']))<div class="logo"><img src="{{ $brand['logo_url'] }}" alt="{{ $brand['short_name'] ?? 'COU Youth Platform' }} logo"></div>@else<div class="logo fallback">COU</div>@endif
    <div class="code">{{ $status }}</div>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    <div class="actions">
        <a class="btn" href="javascript:location.reload()">Try again</a>
        <a class="btn btn-primary" href="{{ route('home') }}">Go to website</a>
    </div>
</main>
</body>
</html>
