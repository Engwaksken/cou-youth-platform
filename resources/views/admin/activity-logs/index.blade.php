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
        <input type="hidden" name="per_page" value="{{ $perPage }}">
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
        <div class="pagination activity-pagination">
            <form class="activity-page-size" method="GET" action="{{ route('admin.activity-logs.index') }}">
                @foreach($filters as $name => $value)
                    @if(filled($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                @endforeach
                <span>{{ $logs->total() === 0 ? 'No entries' : 'Showing '.number_format($logs->firstItem()).'–'.number_format($logs->lastItem()).' of '.number_format($logs->total()) }}</span>
                <label>Per page
                    <select name="per_page" onchange="this.form.submit()">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <noscript><button class="btn btn-light" type="submit">Apply</button></noscript>
            </form>
            @if($logs->hasPages()){{ $logs->onEachSide(1)->links() }}@endif
        </div>
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

@push('styles')<style>.filters{margin-bottom:12px}.muted{color:var(--muted)}td code{font-size:.8125rem;word-break:break-all}.activity-pagination{display:flex;flex-direction:column;gap:12px;border-top:1px solid var(--border)}.activity-page-size{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;color:var(--muted);font-size:.875rem}.activity-page-size label{display:flex;align-items:center;gap:8px;font-weight:600}.activity-page-size select{border:1px solid #d0d5dd;border-radius:8px;padding:6px 8px;background:#fff}</style>@endpush
