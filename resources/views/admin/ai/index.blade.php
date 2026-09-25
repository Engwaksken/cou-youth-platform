@extends('admin.layout')
@section('title','AI Settings')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-robot"></i> AI Settings</h1>
        <p>Manage multiple AI configurations, choose one active connection, and monitor platform usage.</p>
    </div>
    <div class="actions">
        <form method="POST" action="{{ route('admin.ai.test') }}">
            @csrf
            <button class="btn btn-light" type="submit" @disabled(!$activeSetting)>
                <i class="fas fa-plug-circle-check"></i> Test Active Connection
            </button>
        </form>
        <button class="btn btn-primary" type="button" data-open-modal="ai-setting-create">
            <i class="fas fa-plus"></i> Add AI Setting
        </button>
    </div>
</div>

@if($errors->has('ai_test'))
    <div class="alert alert-danger">{{ $errors->first('ai_test') }}</div>
@endif

<div data-tabs>
    <div class="tabs">
        <button class="tab active" data-tab-target="ai-settings">
            <i class="fas fa-sliders"></i> AI Settings <span class="tab-count">{{ $settings->count() }}</span>
        </button>
        <button class="tab" data-tab-target="ai-overview">
            <i class="fas fa-chart-column"></i> Overview
        </button>
        <button class="tab" data-tab-target="ai-usage">
            <i class="fas fa-list"></i> Usage <span class="tab-count">{{ $recentUsage->total() }}</span>
        </button>
    </div>

    <section id="ai-settings" class="tab-panel active">
        <div class="card active-ai-card">
            <div>
                <small>ACTIVE AI CONNECTION</small>
                @if($activeSetting)
                    <h3>{{ strtoupper($activeSetting->provider) }} / {{ $activeSetting->model }}</h3>
                    <p>Only this active setting is used by the platform. Other saved settings remain inactive until an administrator activates them.</p>
                @else
                    <h3>No active AI setting</h3>
                    <p>AI features will remain unavailable until an administrator activates one saved configuration.</p>
                @endif
            </div>
            <span class="badge {{ $activeSetting ? 'badge-success' : 'badge-danger' }}">
                {{ $activeSetting ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <form id="bulkSettingsForm" method="POST" action="{{ route('admin.ai.settings.bulk-destroy') }}" class="card table-card">
            @csrf
            @method('DELETE')
            <div class="table-toolbar">
                <div>
                    <strong>Saved AI configurations</strong>
                    <small>Store multiple providers or models and activate only the one that should serve requests.</small>
                </div>
                <button type="submit" class="btn btn-danger" id="deleteSelectedSettings" disabled onclick="return confirm('Delete the selected AI settings?');">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="check-col"><input type="checkbox" data-check-all="settings" aria-label="Select all AI settings"></th>
                            <th>Provider / Model</th>
                            <th>Endpoint</th>
                            <th>Limits</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" data-check-item="settings" aria-label="Select {{ $item->provider }} {{ $item->model }}"></td>
                                <td>
                                    <strong>{{ strtoupper($item->provider) }}</strong>
                                    <small>{{ $item->model }}</small>
                                </td>
                                <td>{{ $item->api_endpoint ?: 'Default provider endpoint' }}</td>
                                <td>
                                    <span>{{ number_format((int) $item->daily_limit) }} daily</span>
                                    <small>{{ number_format((int) $item->monthly_limit) }} monthly / {{ number_format((int) $item->per_user_daily_limit) }} per user</small>
                                </td>
                                <td>
                                    <span class="badge {{ $item->is_enabled ? 'badge-success' : 'badge-muted' }}">
                                        {{ $item->is_enabled ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ optional($item->updated_at)->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="row-actions">
                                        @unless($item->is_enabled)
                                            <form method="POST" action="{{ route('admin.ai.settings.activate', $item) }}">
                                                @csrf
                                                <button class="icon-btn success" type="submit" title="Activate">
                                                    <i class="fas fa-circle-check"></i>
                                                </button>
                                            </form>
                                        @endunless
                                        <button class="icon-btn" type="button" data-open-modal="ai-setting-edit-{{ $item->id }}" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.ai.settings.destroy', $item) }}" onsubmit="return confirm('Delete this AI setting?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-btn danger" type="submit" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty" colspan="7">No AI settings have been added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </section>

    <section id="ai-overview" class="tab-panel">
        <div class="grid stats-grid">
            <div class="card stat-card stat-purple"><i class="fas fa-bolt stat-icon"></i><div><strong>{{ number_format($usage['today_requests']) }}</strong><span>Requests today</span></div></div>
            <div class="card stat-card stat-blue"><i class="fas fa-calendar-days stat-icon"></i><div><strong>{{ number_format($usage['month_requests']) }}</strong><span>This month</span></div></div>
            <div class="card stat-card stat-green"><i class="fas fa-circle-check stat-icon"></i><div><strong>{{ number_format($usage['successful']) }}</strong><span>Successful</span></div></div>
            <div class="card stat-card stat-red"><i class="fas fa-triangle-exclamation stat-icon"></i><div><strong>{{ number_format($usage['failed']) }}</strong><span>Failed</span></div></div>
        </div>
        <div class="card">
            <h3 style="margin-top:0">AI requests — last 7 days</h3>
            <div class="mini-bars">
                @php $max=max(1,(int)$chart->max('total')); @endphp
                @foreach($chart as $point)
                    <div class="mini-row"><span>{{ $point['label'] }}</span><div class="mini-track"><i style="height:{{ ($point['total']/$max)*100 }}%"></i></div><strong>{{ $point['total'] }}</strong></div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="ai-usage" class="tab-panel">
        <div class="card">
            <form class="filters" method="get" action="{{ route('admin.ai.index') }}">
                <input name="q" value="{{ request('q') }}" placeholder="Search user, module, provider or model">
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="successful" @selected(request('status')==='successful')>Successful</option>
                    <option value="failed" @selected(request('status')==='failed')>Failed</option>
                </select>
                <select name="module">
                    <option value="">All modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('module')===$module)>{{ ucwords(str_replace('_',' ',$module)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a class="btn btn-light" href="{{ route('admin.ai.index') }}#ai-usage">Reset</a>
            </form>
        </div>

        <form id="bulkUsageForm" method="POST" action="{{ route('admin.ai.usage.bulk-destroy') }}" class="card table-card">
            @csrf
            @method('DELETE')
            <div class="table-toolbar">
                <div>
                    <strong>AI usage history</strong>
                    <small>Token counts: {{ number_format($usage['input_tokens']) }} input / {{ number_format($usage['output_tokens']) }} output.</small>
                </div>
                <button type="submit" class="btn btn-danger" id="deleteSelectedUsage" disabled onclick="return confirm('Delete the selected AI usage records?');">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="check-col"><input type="checkbox" data-check-all="usage" aria-label="Select all usage rows"></th>
                            <th>Date</th><th>User</th><th>Module</th><th>Provider / Model</th><th>Tokens</th><th>Duration</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsage as $log)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $log->id }}" data-check-item="usage" aria-label="Select usage row {{ $log->id }}"></td>
                                <td>{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ optional($log->user)->name ?: 'System/Guest' }}<small>{{ optional($log->user)->email }}</small></td>
                                <td>{{ ucfirst(str_replace('_',' ',$log->module)) }}</td>
                                <td>{{ $log->provider ?: '—' }}<small>{{ $log->model ?: '—' }}</small></td>
                                <td>{{ number_format((int)$log->input_tokens) }} / {{ number_format((int)$log->output_tokens) }}</td>
                                <td>{{ $log->duration_ms ? number_format($log->duration_ms).' ms' : '—' }}</td>
                                <td><span class="badge {{ $log->successful?'badge-success':'badge-danger' }}">{{ $log->successful?'Successful':'Failed' }}</span>@if(!$log->successful)<small>{{ $log->error_code ?: 'Provider error' }}</small>@endif</td>
                            </tr>
                        @empty
                            <tr><td class="empty" colspan="8">No AI usage found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $recentUsage->withQueryString()->links() }}</div>
        </form>
    </section>
</div>

<div id="ai-setting-create" class="modal">
    <div class="modal-card large">
        <div class="modal-head"><h2>Add AI Setting</h2><button class="icon-btn" type="button" data-close-modal><i class="fas fa-xmark"></i></button></div>
        <form method="POST" action="{{ route('admin.ai.settings.store') }}">
            @csrf
            @include('admin.ai.partials.setting-form', ['setting' => null, 'creating' => true])
            <div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save AI Setting</button></div>
        </form>
    </div>
</div>

@foreach($settings as $item)
    <div id="ai-setting-edit-{{ $item->id }}" class="modal">
        <div class="modal-card large">
            <div class="modal-head"><h2>Edit AI Setting</h2><button class="icon-btn" type="button" data-close-modal><i class="fas fa-xmark"></i></button></div>
            <form method="POST" action="{{ route('admin.ai.settings.update', $item) }}">
                @csrf
                @method('PUT')
                @include('admin.ai.partials.setting-form', ['setting' => $item, 'creating' => false])
                <div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Update AI Setting</button></div>
            </form>
        </div>
    </div>
@endforeach
@endsection

@push('styles')
<style>
    .active-ai-card{display:flex;align-items:center;justify-content:space-between;gap:20px;border-left:5px solid var(--primary)}
    .active-ai-card small{display:block;color:var(--muted);font-weight:800;letter-spacing:.04em}.active-ai-card h3{margin:5px 0}.active-ai-card p{margin:0;color:var(--muted)}
    .table-toolbar{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:14px}.table-toolbar small{display:block;color:var(--muted);margin-top:4px}.check-col{width:42px}.row-actions{display:flex;align-items:center;gap:6px}.row-actions form{margin:0}.icon-btn.success{color:#15803d}.icon-btn.danger{color:#b91c1c}.badge-muted{background:#eef2f7;color:#475467}.btn-danger{background:#b42318!important;border-color:#b42318!important;color:#fff!important}.btn-danger:disabled{opacity:.45;cursor:not-allowed}
    .stat-card{border-left:5px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-blue{border-left-color:#2563eb}.stat-green{border-left-color:#16a34a}.stat-red{border-left-color:#dc2626}
    .mini-bars{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:12px;align-items:end}.mini-row{display:grid;grid-template-rows:auto 85px auto;gap:7px;text-align:center}.mini-track{height:85px;background:#eef2f7;border-radius:9px;overflow:hidden;display:flex;align-items:flex-end}.mini-track i{display:block;width:100%;background:var(--primary);border-radius:9px 9px 0 0}
    .field-help{display:block;color:var(--muted);margin-top:5px}
    @media(max-width:800px){.mini-bars{grid-template-columns:repeat(4,1fr)}.table-toolbar,.active-ai-card{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupBulk(group, buttonId) {
            const master = document.querySelector('[data-check-all="' + group + '"]');
            const items = Array.from(document.querySelectorAll('[data-check-item="' + group + '"]'));
            const button = document.getElementById(buttonId);
            if (!button || items.length === 0) return;

            const sync = function () {
                const checked = items.filter(function (item) { return item.checked; }).length;
                button.disabled = checked === 0;
                if (master) {
                    master.checked = checked === items.length && items.length > 0;
                    master.indeterminate = checked > 0 && checked < items.length;
                }
            };

            if (master) {
                master.addEventListener('change', function () {
                    items.forEach(function (item) { item.checked = master.checked; });
                    sync();
                });
            }

            items.forEach(function (item) { item.addEventListener('change', sync); });
            sync();
        }

        setupBulk('settings', 'deleteSelectedSettings');
        setupBulk('usage', 'deleteSelectedUsage');

        if (window.location.hash === '#ai-usage') {
            document.querySelector('[data-tab-target="ai-usage"]')?.click();
        }
    });
</script>
@endpush
