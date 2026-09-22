@php
    $brand = $brand ?? app(\App\Services\Branding\BrandingService::class)->data();
    $primary = $brand['primary_color'] ?? '#4B2E83';
    $secondary = $brand['secondary_color'] ?? '#204F78';
    $name = $brand['name'] ?? 'Church of Uganda Youth Platform';
    $tagline = $brand['tagline'] ?? 'Connecting Young People. Growing Disciples. Transforming Nations.';
    $supportEmail = $brand['support_email'] ?? null;
    $logoUrl = $brand['logo_url'] ?? null;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $subjectLine ?? $name }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6fb;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 30px rgba(31,41,55,.08);">
                <tr>
                    <td style="padding:22px 28px;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});color:#ffffff;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="vertical-align:middle;width:74px;">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="{{ $name }} logo" style="display:block;max-width:64px;max-height:64px;width:auto;height:auto;background:#ffffff;border-radius:12px;padding:5px;object-fit:contain;">
                                    @endif
                                </td>
                                <td style="vertical-align:middle;padding-left:12px;">
                                    <div style="font-size:20px;font-weight:800;line-height:1.2;">{{ $name }}</div>
                                    <div style="margin-top:5px;font-size:12px;line-height:1.5;color:rgba(255,255,255,.88);">{{ $tagline }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px;">
                        @if(!empty($recipientName))
                            <p style="margin:0 0 14px;font-size:15px;">Dear {{ $recipientName }},</p>
                        @endif
                        <h1 style="margin:0 0 18px;font-size:24px;line-height:1.25;color:#111827;">{{ $heading ?? $subjectLine ?? 'Platform update' }}</h1>
                        <div style="font-size:15px;line-height:1.75;color:#374151;white-space:pre-line;">{{ $messageBody }}</div>

                        @if(!empty($actionUrl))
                            <p style="margin:26px 0 4px;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 18px;border-radius:10px;background:{{ $primary }};color:#ffffff;text-decoration:none;font-weight:700;">{{ $actionLabel ?? 'Open COU Youth Platform' }}</a>
                            </p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 28px;background:#f8fafc;border-top:1px solid #e5e7eb;color:#667085;font-size:12px;line-height:1.6;">
                        <strong style="color:#374151;">{{ $name }}</strong><br>
                        {{ $tagline }}
                        @if($supportEmail)
                            <br>Support: <a href="mailto:{{ $supportEmail }}" style="color:{{ $primary }};">{{ $supportEmail }}</a>
                        @endif
                        <br><span style="color:#98a2b3;">This is an official message from the Church of Uganda Youth Platform.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
