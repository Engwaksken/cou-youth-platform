@extends('admin.layout')

@section('title', 'Payment Gateways | COU Youth CMS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-credit-card"></i> Payment Gateways</h1>
        <p>Configure payment providers, credentials, callbacks and operating mode.</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-modal="gateway-create-modal">
        <i class="fas fa-plus"></i> Add Gateway
    </button>
</div>

<div class="grid stats-grid gateway-stats">
    <div class="card stat-card">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div><strong>{{ number_format($stats['total'] ?? 0) }}</strong><span>Total gateways</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        <div><strong>{{ number_format($stats['enabled'] ?? 0) }}</strong><span>Enabled</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="fas fa-satellite-dish"></i></div>
        <div><strong>{{ number_format($stats['live'] ?? 0) }}</strong><span>Live mode</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="fas fa-flask"></i></div>
        <div><strong>{{ number_format($stats['test'] ?? 0) }}</strong><span>Test mode</span></div>
    </div>
</div>

<div class="card gateway-note">
    <div class="gateway-note-icon"><i class="fas fa-shield-halved"></i></div>
    <div>
        <strong>Credential security</strong>
        <p>API credentials are encrypted at rest and are never displayed back in the CMS or returned by public APIs. Leave credential fields blank when editing to keep the saved values.</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.payment-gateways.index') }}" class="card gateway-filters">
    <div class="filter-search">
        <i class="fas fa-magnifying-glass"></i>
        <input
            type="search"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            placeholder="Search name, provider, slug or currency"
            aria-label="Search payment gateways"
        >
    </div>

    <select name="status" aria-label="Filter by status">
        <option value="">All statuses</option>
        <option value="enabled" @selected(($filters['status'] ?? '') === 'enabled')>Enabled</option>
        <option value="disabled" @selected(($filters['status'] ?? '') === 'disabled')>Disabled</option>
    </select>

    <select name="mode" aria-label="Filter by mode">
        <option value="">All modes</option>
        <option value="live" @selected(($filters['mode'] ?? '') === 'live')>Live</option>
        <option value="test" @selected(($filters['mode'] ?? '') === 'test')>Test</option>
    </select>

    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>

    @if(($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || ($filters['mode'] ?? '') !== '')
        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-light"><i class="fas fa-xmark"></i> Clear</a>
    @endif
</form>

<div class="card table-card gateway-table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Gateway</th>
                    <th>Provider</th>
                    <th>Currency</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Configuration</th>
                    <th>Order</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $gateway)
                    @php
                        $settings = is_array($gateway->settings) ? $gateway->settings : [];
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $gateway->name }}</strong>
                            <small>{{ $gateway->slug }}</small>
                        </td>
                        <td>{{ $gateway->provider }}</td>
                        <td><span class="badge">{{ strtoupper($gateway->currency) }}</span></td>
                        <td>
                            @if($gateway->is_test_mode)
                                <span class="badge badge-warning"><i class="fas fa-flask"></i>&nbsp; Test</span>
                            @else
                                <span class="badge badge-success"><i class="fas fa-satellite-dish"></i>&nbsp; Live</span>
                            @endif
                        </td>
                        <td>
                            @if($gateway->is_enabled)
                                <span class="badge badge-success">Enabled</span>
                            @else
                                <span class="badge">Disabled</span>
                            @endif
                        </td>
                        <td>
                            <div class="configuration-list">
                                <span title="API credentials"><i class="fas fa-key"></i> Encrypted credentials</span>
                                @if(!empty($settings['callback_url']))
                                    <span title="Callback URL"><i class="fas fa-arrow-rotate-left"></i> Callback</span>
                                @endif
                                @if(!empty($settings['webhook_url']))
                                    <span title="Webhook URL"><i class="fas fa-code-branch"></i> Webhook</span>
                                @endif
                                @if(!empty($settings['initialize_url']))
                                    <span title="Initialize URL"><i class="fas fa-link"></i> Initialize URL</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ number_format((int) $gateway->sort_order) }}</td>
                        <td>
                            <div class="row-actions">
                                <button
                                    type="button"
                                    class="icon-btn"
                                    title="Edit {{ $gateway->name }}"
                                    aria-label="Edit {{ $gateway->name }}"
                                    data-open-modal="gateway-edit-{{ $gateway->id }}"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.payment-gateways.destroy', $gateway) }}"
                                    class="delete-gateway-form"
                                    data-name="{{ $gateway->name }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="icon-btn danger-icon-btn"
                                        title="Delete {{ $gateway->name }}"
                                        aria-label="Delete {{ $gateway->name }}"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">
                            <i class="fas fa-credit-card gateway-empty-icon"></i>
                            <strong>No payment gateways found.</strong>
                            <span>Add a gateway or adjust your filters.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div class="pagination">{{ $items->links() }}</div>
    @endif
</div>

{{-- Create gateway modal --}}
<div class="modal" id="gateway-create-modal" aria-hidden="true">
    <div class="modal-card gateway-modal-card" role="dialog" aria-modal="true" aria-labelledby="gateway-create-title">
        <div class="modal-head">
            <div>
                <h2 id="gateway-create-title"><i class="fas fa-plus-circle"></i> Add Payment Gateway</h2>
                <p>Configure a payment provider. Start in test mode until integration is verified.</p>
            </div>
            <button type="button" class="icon-btn" data-close-modal aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>

        <form method="POST" action="{{ route('admin.payment-gateways.store') }}">
            @csrf
            @include('admin.payment_gateways.partials.form', [
                'gateway' => null,
                'settings' => [],
                'submitLabel' => 'Add Gateway',
                'submitIcon' => 'fa-plus',
            ])
        </form>
    </div>
</div>

{{-- Edit gateway modals --}}
@foreach($items as $gateway)
    @php $gatewaySettings = is_array($gateway->settings) ? $gateway->settings : []; @endphp
    <div class="modal" id="gateway-edit-{{ $gateway->id }}" aria-hidden="true">
        <div class="modal-card gateway-modal-card" role="dialog" aria-modal="true" aria-labelledby="gateway-edit-title-{{ $gateway->id }}">
            <div class="modal-head">
                <div>
                    <h2 id="gateway-edit-title-{{ $gateway->id }}"><i class="fas fa-pen-to-square"></i> Edit {{ $gateway->name }}</h2>
                    <p>Update gateway configuration. Saved credentials remain encrypted.</p>
                </div>
                <button type="button" class="icon-btn" data-close-modal aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.payment-gateways.update', $gateway) }}">
                @csrf
                @method('PUT')
                @include('admin.payment_gateways.partials.form', [
                    'gateway' => $gateway,
                    'settings' => $gatewaySettings,
                    'submitLabel' => 'Save Changes',
                    'submitIcon' => 'fa-floppy-disk',
                ])
            </form>
        </div>
    </div>
@endforeach

<style>
    .gateway-stats .stat-card { min-height: 92px; }
    .gateway-note { display:flex; align-items:flex-start; gap:14px; margin-bottom:16px; border-left:4px solid var(--primary); }
    .gateway-note-icon { width:42px; height:42px; border-radius:10px; display:grid; place-items:center; flex:0 0 42px; background:#ede9fe; color:var(--primary); }
    .gateway-note p { margin:5px 0 0; color:var(--muted); line-height:1.55; }
    .gateway-filters { display:grid; grid-template-columns:minmax(260px,2fr) minmax(150px,1fr) minmax(150px,1fr) auto auto; gap:10px; align-items:center; margin-bottom:16px; }
    .gateway-filters input,.gateway-filters select { width:100%; border:1px solid #d1d5db; border-radius:9px; padding:10px; background:#fff; min-height:42px; }
    .filter-search { position:relative; }
    .filter-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; }
    .filter-search input { padding-left:36px; }
    .gateway-table-card { margin-top:0; }
    .configuration-list { display:flex; flex-direction:column; gap:5px; color:var(--muted); font-size:12px; }
    .configuration-list i { width:15px; color:var(--primary); }
    .row-actions { display:flex; gap:7px; align-items:center; }
    .row-actions form { margin:0; }
    .danger-icon-btn { color:var(--danger); }
    .gateway-empty-icon { display:block; font-size:30px; color:var(--primary); margin-bottom:9px; }
    .empty strong,.empty span { display:block; }
    .empty span { margin-top:5px; }
    .gateway-modal-card { width:min(860px,100%); }
    .gateway-modal-card .modal-head { align-items:flex-start; }
    .gateway-modal-card .modal-head p { margin:5px 0 0; color:var(--muted); font-size:13px; }
    .gateway-modal-card h2 i { color:var(--primary); margin-right:6px; }
    @media(max-width:1000px){ .gateway-filters{grid-template-columns:1fr 1fr;} .filter-search{grid-column:1/-1;} }
    @media(max-width:640px){ .gateway-filters{grid-template-columns:1fr;} .filter-search{grid-column:auto;} }
</style>

<script>
(() => {
    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.modal.open')) document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.openModal));
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.closest('.modal')));
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.querySelectorAll('.modal.open').forEach(closeModal);
    });

    document.querySelectorAll('.delete-gateway-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const name = form.dataset.name || 'this payment gateway';
            if (!window.confirm(`Delete ${name}? This action cannot be undone.`)) event.preventDefault();
        });
    });

    @if($errors->any())
        openModal('gateway-create-modal');
    @endif
})();
</script>
@endsection
