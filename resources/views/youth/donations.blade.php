@extends('youth.layout')
@section('title', 'Donations & Contributions | COU Youth')
@section('youth_content')
<section class="hero"><span class="section-kicker">GIVING</span><h1>Donations & Contributions</h1><p>Support youth ministry securely from your account and review your personal giving history in one place.</p></section>

@if($errors->any())
<div class="form-errors" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="page-stats">
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-hand-holding-heart"></i></span><div><strong>UGX {{ number_format($stats['successful_ugx'],0) }}</strong><span>Successful giving</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-receipt"></i></span><div><strong>{{ $stats['donation_count'] }}</strong><span>Donation records</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-clock"></i></span><div><strong>{{ $stats['pending'] }}</strong><span>Pending payments</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-coins"></i></span><div><strong>{{ $stats['other_contributions'] }}</strong><span>Other contributions</span></div></div>
</div>

<div class="giving-grid">
    <section class="card giving-form-card">
        <div class="section-head"><div><span class="section-kicker">MAKE A DONATION</span><h2>Give to youth ministry</h2><p class="muted">Choose a campaign and available payment method. Mobile Money payments will send a prompt to your phone.</p></div></div>
        @if($gateways->isEmpty())
            <div class="empty-note"><i class="fas fa-circle-info"></i><span>No payment method is currently enabled. Please check again later.</span></div>
        @else
        <form method="POST" action="{{ route('youth.donations.store') }}" class="giving-form">
            @csrf
            <label>Campaign
                <select name="campaign_id">
                    <option value="">General youth ministry support</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((string)old('campaign_id',$selectedCampaign)===(string)$campaign->id)>{{ $campaign->title }} · {{ $campaign->currency }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-grid">
                <label>Amount
                    <input type="number" name="amount" min="100" step="100" value="{{ old('amount') }}" placeholder="e.g. 50000" required>
                </label>
                <label>Currency
                    <select name="currency" required>
                        @foreach($gateways->pluck('currency')->filter()->unique()->values() as $currency)
                            <option value="{{ strtoupper($currency) }}" @selected(old('currency','UGX')===strtoupper($currency))>{{ strtoupper($currency) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>Payment method
                <select name="payment_gateway_id" required>
                    <option value="">Select payment method</option>
                    @foreach($gateways as $gateway)
                        <option value="{{ $gateway->id }}" @selected((string)old('payment_gateway_id')===(string)$gateway->id)>{{ $gateway->name }} · {{ strtoupper($gateway->currency) }}{{ $gateway->is_test_mode ? ' (Test)' : '' }}</option>
                    @endforeach
                </select>
            </label>
            <label>Mobile Money / payment phone
                <input type="tel" name="donor_phone" value="{{ old('donor_phone') }}" maxlength="40" placeholder="e.g. 2567XXXXXXXX">
                <small>Required for MTN MoMo, Airtel Money and mobile-money prompt payments.</small>
            </label>
            <label class="anonymous-option"><input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))><span><strong>Give anonymously</strong><small>Your account will still securely own the transaction, but your name will not be shown as the donor where the campaign allows anonymous giving.</small></span></label>
            <button class="btn btn-primary btn-wide" type="submit"><i class="fas fa-hand-holding-dollar"></i> Start secure donation</button>
        </form>
        @endif
    </section>

    <aside class="card giving-help">
        <span class="help-icon"><i class="fas fa-shield-halved"></i></span><h2>Secure giving</h2>
        <p class="muted">Your donation is linked only to your signed-in youth account. Payment-provider credentials remain server-side and are never displayed in this portal.</p>
        <div class="help-points"><span><i class="fas fa-user-shield"></i> Personal transaction history</span><span><i class="fas fa-mobile-screen-button"></i> Mobile Money prompts where supported</span><span><i class="fas fa-receipt"></i> References and receipt numbers retained</span><span><i class="fas fa-lock"></i> Other users cannot view your giving history</span></div>
    </aside>
</div>

<div class="history-tabs" role="tablist">
    <button class="history-tab active" type="button" data-history="donations"><i class="fas fa-hand-holding-heart"></i> Donation History</button>
    <button class="history-tab" type="button" data-history="contributions"><i class="fas fa-coins"></i> Other Contributions</button>
</div>

<section class="history-panel active" id="history-donations">
    <div class="card history-card">
        <div class="section-head"><div><span class="section-kicker">HISTORY</span><h2>My donations</h2></div></div>
        <div class="history-list">
        @if($donations instanceof \Illuminate\Contracts\Pagination\Paginator && $donations->count())
            @foreach($donations as $donation)
            <div class="history-row">
                <span class="history-icon"><i class="fas fa-hand-holding-heart"></i></span>
                <div><strong>{{ $donation->campaign?->title ?? 'General youth ministry support' }}</strong><small>{{ optional($donation->paid_at ?: $donation->created_at)->format('d M Y · H:i') }}</small><small>Ref: {{ $donation->receipt_number ?: $donation->reference }}</small></div>
                <div class="history-value"><strong>{{ $donation->currency }} {{ number_format((float)$donation->amount,0) }}</strong><span class="status status-{{ strtolower($donation->status) }}">{{ ucfirst($donation->status) }}</span></div>
            </div>
            @endforeach
            @if($donations->hasPages())<div class="pagination">{{ $donations->appends(['history'=>'donations'])->links() }}</div>@endif
        @else
            <div class="empty-state"><i class="fas fa-hand-holding-heart"></i><h3>No donation history yet</h3><p class="muted">Your giving records will appear here after you start a donation.</p></div>
        @endif
        </div>
    </div>
</section>

<section class="history-panel" id="history-contributions">
    <div class="card history-card">
        <div class="section-head"><div><span class="section-kicker">OTHER CONTRIBUTIONS</span><h2>Youth ministry payments</h2></div></div>
        <div class="history-list">
        @if($membershipPayments && $membershipPayments->count())
            @foreach($membershipPayments as $payment)
            <div class="history-row">
                <span class="history-icon"><i class="fas fa-coins"></i></span>
                <div><strong>{{ $payment->fee_name ?: 'Youth ministry contribution' }}@if($payment->fee_year) · {{ $payment->fee_year }}@endif</strong><small>{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y · H:i') }}</small>@if($payment->reference)<small>Ref: {{ $payment->reference }}</small>@endif</div>
                <div class="history-value"><strong>{{ $payment->currency }} {{ number_format((float)$payment->amount,0) }}</strong><span class="status status-{{ strtolower($payment->status) }}">{{ ucfirst($payment->status) }}</span></div>
            </div>
            @endforeach
            @if($membershipPayments->hasPages())<div class="pagination">{{ $membershipPayments->appends(['history'=>'contributions'])->links() }}</div>@endif
        @else
            <div class="empty-state"><i class="fas fa-coins"></i><h3>No other contributions yet</h3><p class="muted">Membership fees and other youth-ministry payments linked to your account will appear here.</p></div>
        @endif
        </div>
    </div>
</section>

<style>
.form-errors{padding:13px 15px;border-radius:10px;background:#fff1f0;border:1px solid #f5b7b1;color:#8a1c16;margin-bottom:18px}.giving-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.7fr);gap:18px;align-items:start;margin-bottom:28px}.section-head h2{margin:3px 0 5px}.section-head p{margin:0}.giving-form{display:grid;gap:14px}.giving-form label{font-size:.84rem;font-weight:800;color:#344054}.giving-form select,.giving-form input{width:100%;margin-top:6px;border:1px solid #d0d5dd;border-radius:10px;padding:11px 12px;background:#fff}.giving-form label>small{display:block;margin-top:5px;color:var(--muted);font-weight:500}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.anonymous-option{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface-soft)}.anonymous-option input{width:auto;margin:4px 0 0}.anonymous-option strong,.anonymous-option small{display:block}.anonymous-option small{margin-top:3px;color:var(--muted);font-weight:500}.empty-note{display:flex;gap:10px;padding:14px;border-radius:10px;background:#fffaeb;color:#92400e}.giving-help{position:sticky;top:88px}.help-icon{width:48px;height:48px;border-radius:12px;background:var(--primary-soft);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.15rem}.help-icon i,.history-icon i{line-height:1}.giving-help h2{margin:14px 0 6px}.help-points{display:grid;gap:11px;margin-top:18px}.help-points span{display:flex;gap:9px;align-items:center;font-weight:700;font-size:.88rem}.help-points i{width:18px;color:var(--primary)}.history-tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin-bottom:16px;overflow-x:auto}.history-tab{border:0;background:transparent;padding:10px 13px;font-weight:800;color:var(--muted);border-bottom:3px solid transparent;cursor:pointer;white-space:nowrap}.history-tab:hover,.history-tab.active{color:var(--primary);background:var(--primary-soft)}.history-tab.active{border-color:var(--primary)}.history-panel{display:none}.history-panel.active{display:block}.history-card{padding:18px}.history-list{display:grid;gap:9px}.history-row{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:12px;align-items:center;padding:11px;border:1px solid var(--border);border-radius:10px;background:#fff}.history-icon{width:40px;height:40px;border-radius:10px;background:var(--primary-soft);color:var(--primary);display:flex;align-items:center;justify-content:center}.history-row strong,.history-row small{display:block}.history-row small{font-size:.75rem;color:var(--muted);margin-top:2px}.history-value{text-align:right}.status{display:inline-flex;margin-top:4px;padding:3px 7px;border-radius:999px;background:#f2f4f7;color:#475467;font-size:.68rem;font-weight:800}.status-paid,.status-completed,.status-successful,.status-success{background:#ecfdf3;color:#067647}.status-pending{background:#fffaeb;color:#b54708}.status-failed,.status-cancelled{background:#fff1f3;color:#c01048}.empty-state{text-align:center;padding:28px}.empty-state>i{font-size:1.8rem;color:var(--primary);margin-bottom:8px}@media(max-width:900px){.giving-grid{grid-template-columns:1fr}.giving-help{position:static}}@media(max-width:620px){.form-grid{grid-template-columns:1fr}.history-row{grid-template-columns:auto 1fr}.history-value{grid-column:2;text-align:left}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{const requested=new URL(window.location.href).searchParams.get('history');if(requested==='contributions'){document.querySelectorAll('.history-tab').forEach(b=>b.classList.toggle('active',b.dataset.history==='contributions'));document.querySelectorAll('.history-panel').forEach(p=>p.classList.toggle('active',p.id==='history-contributions'));}document.querySelectorAll('.history-tab').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.history-tab').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.history-panel').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.getElementById('history-'+btn.dataset.history)?.classList.add('active')}));});
</script>
@endsection
