@extends('admin.layout')

@section('title', 'Donations & Campaigns')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-hand-holding-heart"></i> Donations & Campaigns</h1>
        <p>Manage donation campaigns and review incoming transactions.</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="document.getElementById('campaignModal').classList.add('open')">
        <i class="fas fa-plus"></i> New Campaign
    </button>
</div>

<div class="grid stats-grid">
    <div class="card stat-card"><i class="fas fa-coins stat-icon"></i><div><strong>{{ number_format($summary['total']) }}</strong><span>Total Donations</span></div></div>
    <div class="card stat-card"><i class="fas fa-circle-check stat-icon"></i><div><strong>{{ number_format($summary['successful']) }}</strong><span>Successful</span></div></div>
    <div class="card stat-card"><i class="fas fa-clock stat-icon"></i><div><strong>{{ number_format($summary['pending']) }}</strong><span>Pending</span></div></div>
    <div class="card stat-card"><i class="fas fa-money-bill-trend-up stat-icon"></i><div><strong>UGX {{ number_format($summary['amount_raised'], 0) }}</strong><span>Raised</span></div></div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="transactions"><i class="fas fa-receipt"></i> Transactions</button>
    <button class="tab" data-tab="campaigns"><i class="fas fa-bullseye"></i> Campaigns</button>
</div>

<section id="transactions" class="tab-panel active">
    <div class="card">
        <form method="GET" class="filters">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search reference, donor, receipt...">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['pending','successful','paid','completed','failed','cancelled'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <select name="campaign_id">
                <option value="">All campaigns</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected($campaignId === $campaign->id)>{{ $campaign->title }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a class="btn btn-light" href="{{ route('admin.donations.index') }}"><i class="fas fa-rotate-left"></i> Reset</a>
        </form>
    </div>

    <div class="card table-card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Donor</th><th>Campaign</th><th>Gateway</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($donations as $donation)
                    <tr>
                        <td><strong>{{ $donation->receipt_number ?: Str::limit($donation->reference, 16) }}</strong><small>{{ $donation->external_transaction_id }}</small></td>
                        <td>{{ $donation->is_anonymous ? 'Anonymous' : ($donation->donor_name ?: optional($donation->user)->name ?: '—') }}<small>{{ $donation->is_anonymous ? '' : $donation->donor_email }}</small></td>
                        <td>{{ optional($donation->campaign)->title ?: 'General Donation' }}</td>
                        <td>{{ optional($donation->gateway)->name ?: '—' }}</td>
                        <td><strong>{{ $donation->currency }} {{ number_format((float)$donation->amount, 0) }}</strong></td>
                        <td><span class="badge badge-{{ in_array($donation->status, ['successful','paid','completed']) ? 'success' : ($donation->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($donation->status) }}</span></td>
                        <td>{{ optional($donation->created_at)->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No donations found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $donations->links() }}</div>
    </div>
</section>

<section id="campaigns" class="tab-panel">
    <div class="grid campaign-grid">
        @forelse($campaigns as $campaign)
            @php
                $raised = (float)($campaign->amount_raised ?? 0);
                $target = (float)($campaign->target_amount ?? 0);
                $percent = $target > 0 ? min(100, round(($raised / $target) * 100)) : 0;
            @endphp
            <div class="card campaign-card">
                <div class="campaign-top">
                    <div><h3>{{ $campaign->title }}</h3><span class="badge">{{ ucfirst($campaign->status) }}</span></div>
                    <button class="icon-btn" type="button" onclick='openEditCampaign(@json($campaign))' aria-label="Edit campaign"><i class="fas fa-pen"></i></button>
                </div>
                <p>{{ Str::limit(strip_tags($campaign->description), 140) }}</p>
                <div class="progress"><span style="width: {{ $percent }}%"></span></div>
                <div class="campaign-numbers"><strong>{{ $campaign->currency }} {{ number_format($raised,0) }}</strong><span>of {{ $campaign->target_amount ? $campaign->currency.' '.number_format((float)$campaign->target_amount,0) : 'open target' }}</span></div>
                <div class="campaign-meta"><span><i class="fas fa-calendar"></i> {{ optional($campaign->starts_at)->format('d M Y') ?: 'Any time' }}</span><span><i class="fas fa-user-secret"></i> {{ $campaign->allow_anonymous ? 'Anonymous allowed' : 'Named donors' }}</span></div>
                @if(!$campaign->donations()->exists())
                    <form method="POST" action="{{ route('admin.donations.campaigns.destroy', $campaign) }}" onsubmit="return confirmCampaignDelete(this)">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card empty"><i class="fas fa-hand-holding-heart"></i> No donation campaigns have been created yet.</div>
        @endforelse
    </div>
</section>

<div id="campaignModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head"><h2 id="campaignModalTitle">New Donation Campaign</h2><button type="button" class="icon-btn" onclick="closeCampaignModal()"><i class="fas fa-xmark"></i></button></div>
        <form id="campaignForm" method="POST" action="{{ route('admin.donations.campaigns.store') }}">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST" disabled>
            <div class="form-grid">
                <label class="span-2">Title<input id="campaignTitle" name="title" required maxlength="190"></label>
                <label class="span-2">Description<textarea id="campaignDescription" name="description" rows="4"></textarea></label>
                <label>Target Amount<input id="campaignTarget" name="target_amount" type="number" min="0" step="0.01"></label>
                <label>Currency<input id="campaignCurrency" name="currency" maxlength="3" value="UGX" required></label>
                <label>Starts At<input id="campaignStarts" name="starts_at" type="datetime-local"></label>
                <label>Ends At<input id="campaignEnds" name="ends_at" type="datetime-local"></label>
                <label>Status<select id="campaignStatus" name="status"><option value="draft">Draft</option><option value="published">Published</option><option value="active">Active</option><option value="closed">Closed</option></select></label>
                <label class="checkbox"><input id="campaignAnonymous" type="checkbox" name="allow_anonymous" value="1" checked> Allow anonymous donations</label>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-light" onclick="closeCampaignModal()">Cancel</button><button class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Campaign</button></div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal" aria-hidden="true"><div class="modal-card small"><div class="modal-head"><h2>Delete campaign?</h2><button type="button" class="icon-btn" onclick="closeDeleteModal()"><i class="fas fa-xmark"></i></button></div><p>This campaign will be permanently deleted.</p><div class="modal-actions"><button class="btn btn-light" type="button" onclick="closeDeleteModal()">Cancel</button><button id="confirmDeleteButton" class="btn btn-danger" type="button"><i class="fas fa-trash"></i> Delete</button></div></div></div>

<script>
const tabs = document.querySelectorAll('.tab');
tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(tab.dataset.tab).classList.add('active');
}));
function closeCampaignModal(){ document.getElementById('campaignModal').classList.remove('open'); }
function toLocalInput(value){ if(!value) return ''; const d=new Date(value); if(Number.isNaN(d.getTime())) return ''; const pad=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`; }
function openEditCampaign(campaign){
    const form=document.getElementById('campaignForm');
    form.action=`{{ url('/admin/donations/campaigns') }}/${campaign.id}`;
    document.getElementById('methodField').disabled=false; document.getElementById('methodField').value='PUT';
    document.getElementById('campaignModalTitle').textContent='Edit Donation Campaign';
    document.getElementById('campaignTitle').value=campaign.title||'';
    document.getElementById('campaignDescription').value=campaign.description||'';
    document.getElementById('campaignTarget').value=campaign.target_amount||'';
    document.getElementById('campaignCurrency').value=campaign.currency||'UGX';
    document.getElementById('campaignStarts').value=toLocalInput(campaign.starts_at);
    document.getElementById('campaignEnds').value=toLocalInput(campaign.ends_at);
    document.getElementById('campaignStatus').value=campaign.status||'draft';
    document.getElementById('campaignAnonymous').checked=!!campaign.allow_anonymous;
    document.getElementById('campaignModal').classList.add('open');
}
let pendingDeleteForm=null;
function confirmCampaignDelete(form){ pendingDeleteForm=form; document.getElementById('deleteModal').classList.add('open'); return false; }
function closeDeleteModal(){ pendingDeleteForm=null; document.getElementById('deleteModal').classList.remove('open'); }
document.getElementById('confirmDeleteButton').addEventListener('click',()=>{ if(pendingDeleteForm) pendingDeleteForm.submit(); });
</script>
@endsection
