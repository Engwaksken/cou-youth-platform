@extends('admin.layout')
@section('title', 'Activity Logs')

@section('content')
@php
    $actionLabels = ['login' => 'Signed in', 'logout' => 'Signed out', 'login_failed' => 'Failed sign-in', 'post' => 'Created', 'put' => 'Updated', 'patch' => 'Updated', 'delete' => 'Deleted'];
    $actionBadges = ['login' => 'badge-success', 'login_failed' => 'badge-danger', 'delete' => 'badge-danger', 'put' => 'badge-warning', 'patch' => 'badge-warning'];
    $hasFilters = array_filter($filters) !== [];
@endphp

<div class="page-head">
    <div><h1><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Activity Logs</h1><p>Sign-ins and changes made in the CMS. Activity older than {{ $retentionMonths }} {{ \Illuminate\Support\Str::plural('month', $retentionMonths) }} is archived on the 1st of each month.</p></div>
</div>

<div data-tabs>
<div class="tabs" role="tablist">
    <button class="tab active" type="button" data-tab-target="activity-list"><i class="fas fa-list" aria-hidden="true"></i> Activity <span class="tab-count">{{ $logs->total() }}</span></button>
    <button class="tab" type="button" data-tab-target="activity-archives"><i class="fas fa-box-archive" aria-hidden="true"></i> Monthly archives <span class="tab-count">{{ count($archives) }}</span></button>
</div>

<section id="activity-list" class="tab-panel active">
    <div class="grid stats-grid">
        <div class="card stat-card"><div class="stat-icon"><i class="fas fa-database" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Entries in database</span></div></div>
        <div class="card stat-card"><div class="stat-icon"><i class="fas fa-calendar-day" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['today']) }}</strong><span>Today</span></div></div>
        <div class="card stat-card"><div class="stat-icon"><i class="fas fa-user-lock" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['failed_logins']) }}</strong><span>Failed sign-ins (7 days)</span></div></div>
        <div class="card stat-card"><div class="stat-icon"><i class="fas fa-box-archive" aria-hidden="true"></i></div><div><strong>{{ count($archives) }}</strong><span>Archived months</span></div></div>
    </div>

    <form class="card filters" method="GET" action="{{ route('admin.activity-logs.index') }}">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search user, module or IP" aria-label="Search activity">
        <select name="action" aria-label="Action">
            <option value="">All actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $actionLabels[$action] ?? ucfirst($action) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
        <div class="actions">
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button>
            @if($hasFilters)<a class="btn btn-light" href="{{ route('admin.activity-logs.index') }}">Clear</a>@endif
        </div>
    </form>

    <div class="card table-card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Details</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d M Y, H:i') }}<small>{{ $log->created_at?->diffForHumans() }}</small></td>
                        <td>
                            @if($log->user)
                                <strong>{{ $log->user->name }}</strong><small>{{ $log->user->email }}</small>
                            @elseif(!empty($log->after['email']))
                                <strong>{{ $log->after['email'] }}</strong><small>No matching account</small>
                            @else
                                <span class="muted">System / deleted user</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $actionBadges[$log->action] ?? '' }}">{{ $actionLabels[$log->action] ?? ucfirst($log->action) }}</span></td>
                        <td>{{ $log->module === 'auth' ? 'Authentication' : ucwords(str_replace(['admin.', '-', '_', '.'], ['', ' ', ' ', ' › '], $log->module)) }}</td>
                        <td>
                            @if(!empty($log->after['path']))<code>{{ $log->after['path'] }}</code>@endif
                            <small>{{ $log->ip_address ?? 'Unknown IP' }}</small>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">{{ $hasFilters ? 'No activity matches these filters.' : 'No activity has been recorded yet.' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="pagination">{{ $logs->links() }}</div>@endif
    </div>
</section>

<section id="activity-archives" class="tab-panel">
    <div class="card table-card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Month</th><th>File</th><th>Size</th><th></th></tr></thead>
                <tbody>
                @forelse($archives as $archive)
                    <tr>
                        <td><strong>{{ $archive['month'] }}</strong></td>
                        <td><code>{{ $archive['name'] }}</code></td>
                        <td>{{ number_format(max(1, $archive['size'] / 1024)) }} KB</td>
                        <td><a class="btn btn-light" href="{{ route('admin.activity-logs.archives.download', $archive['name']) }}"><i class="fas fa-download" aria-hidden="true"></i> Download</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">No months have been archived yet. The first archive is created once activity is older than {{ $retentionMonths }} {{ \Illuminate\Support\Str::plural('month', $retentionMonths) }}.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
</div>
@endsection

@push('styles')<style>.filters{margin-bottom:12px}.muted{color:var(--muted)}td code{font-size:.8125rem;word-break:break-all}</style>@endpush
