<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;background:#f5f6fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6fb;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:28px 28px 20px;background:linear-gradient(135deg,{{ $brand['primary_color'] }},{{ $brand['secondary_color'] }});color:#ffffff;text-align:center;">
                        @if(!empty($brand['logo_url']))
                            <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['short_name'] }} logo" style="max-height:82px;max-width:180px;margin:0 auto 14px;display:block;object-fit:contain;background:#ffffff;border-radius:14px;padding:8px;">
                        @endif
                        <div style="font-size:23px;font-weight:700;line-height:1.3;">{{ $brand['short_name'] }}</div>
                        <div style="font-size:13px;line-height:1.5;opacity:.94;margin-top:5px;">{{ $brand['tagline'] }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px;">
                        <h1 style="font-size:22px;margin:0 0 14px;color:#111827;">{{ $heading }}</h1>
                        <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Hello {{ $user->name }},</p>
                        <p style="font-size:15px;line-height:1.7;margin:0 0 20px;">{{ $intro }}</p>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;font-size:30px;letter-spacing:8px;font-weight:800;color:{{ $brand['primary_color'] }};background:#f3effb;border:1px solid #ddd6fe;border-radius:14px;padding:16px 20px;">{{ $code }}</div>
                        </div>
                        <p style="font-size:14px;line-height:1.65;margin:0 0 8px;">This code expires in <strong>{{ $minutes }} minutes</strong>.</p>
                        <p style="font-size:13px;line-height:1.65;margin:0;color:#6b7280;">If you did not request this code, you can safely ignore this email. Never share your verification code with anyone.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;font-size:12px;line-height:1.6;color:#6b7280;">
                        {{ $brand['name'] }}
                        @if(!empty($brand['support_email']))
                            <br>Support: {{ $brand['support_email'] }}
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
