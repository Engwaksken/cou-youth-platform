<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Platform users</title>
<style>
    body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#1f2937;margin:0}
    h1{font-size:16px;margin:0 0 2px}.meta{color:#6b7280;margin:0 0 10px}
    .stats{width:100%;border-collapse:separate;border-spacing:6px 0;margin:0 -6px 12px}
    .stats td{border:1px solid #e5e7eb;border-radius:6px;padding:6px 8px;width:25%}
    .stats strong{display:block;font-size:14px}.stats span{color:#6b7280}
    table.users{width:100%;border-collapse:collapse}
    table.users th{background:#f3f0ff;text-align:left;padding:5px;font-size:8px;text-transform:uppercase}
    table.users td{padding:5px;border-bottom:1px solid #eef0f3;vertical-align:top;word-wrap:break-word}
    .note{color:#92400e;margin-top:8px}
</style>
</head>
<body>
    <h1>Platform users</h1>
    <p class="meta">
        {{ $dioceseName ? $dioceseName.' · ' : 'All dioceses · ' }}{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('user', $total) }} · Generated {{ $generatedAt->format('d M Y H:i') }}
    </p>
    <table class="stats"><tr>
        <td><strong>{{ number_format($stats['total']) }}</strong><span>Total users</span></td>
        <td><strong>{{ number_format($stats['staff']) }}</strong><span>Admin staff</span></td>
        <td><strong>{{ number_format($stats['youth']) }}</strong><span>Youth members</span></td>
        <td><strong>{{ number_format($stats['new']) }}</strong><span>Joined in last 30 days</span></td>
    </tr></table>
    <table class="users">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th><th>Diocese</th><th>Roles</th><th>Joined</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell !== '' ? $cell : '—' }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="8">No users match this filter.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($total > $limit)
        <p class="note">Showing the first {{ number_format($limit) }} of {{ number_format($total) }} users. Use the CSV export for the full list.</p>
    @endif
</body>
</html>
