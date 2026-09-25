@extends('public.layout')
@section('title', 'Prayer & Pastoral Support | COU Youth')
@section('content')
<section class="hero">
<span class="section-kicker">PRAYER & CARE</span>
<h1>Prayer & Pastoral Support</h1>
<p>Share a prayer request securely with the Church of Uganda youth pastoral team and track the status of requests you have submitted.</p>
</section>

<div class="support-grid">
<section class="card prayer-form-card" aria-labelledby="prayer-form-title">
<div class="section-head"><div class="icon-box"><i class="fas fa-hands-praying" aria-hidden="true"></i></div><div><h2 id="prayer-form-title">Submit a prayer request</h2><p class="muted">Your request is handled according to the visibility level you choose.</p></div></div>

@if($errors->any())
<div class="form-errors" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('public.prayer.store') }}" class="prayer-form">
@csrf
<label for="subject">Subject</label>
<input id="subject" name="subject" value="{{ old('subject') }}" maxlength="255" required placeholder="Briefly describe what you would like prayer for">

<label for="message">Prayer request</label>
<textarea id="message" name="message" rows="7" maxlength="5000" required placeholder="Write your prayer request here">{{ old('message') }}</textarea>

<label for="visibility">Who may view this request?</label>
<select id="visibility" name="visibility" required>
<option value="private" @selected(old('visibility', 'private') === 'private')>Private — authorised pastoral staff only</option>
<option value="pastoral_team" @selected(old('visibility') === 'pastoral_team')>Pastoral team</option>
<option value="public_anonymous" @selected(old('visibility') === 'public_anonymous')>Public anonymously</option>
</select>
<p class="privacy-note"><i class="fas fa-shield-halved" aria-hidden="true"></i> Do not include passwords, financial PINs or other secrets. For immediate danger or emergencies, contact local emergency or trusted safeguarding support directly.</p>

<button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane" aria-hidden="true"></i> Submit prayer request</button>
</form>
</section>

<aside class="card pastoral-card">
<div class="icon-box"><i class="fas fa-heart" aria-hidden="true"></i></div>
<h2>You are not alone</h2>
<p class="muted">Prayer requests can be reviewed by authorised pastoral staff. Where follow-up is appropriate, the team can update the request status and pastoral notes through the secure admin area.</p>
<div class="care-points">
<span><i class="fas fa-lock" aria-hidden="true"></i> Controlled visibility</span>
<span><i class="fas fa-user-shield" aria-hidden="true"></i> Pastoral review</span>
<span><i class="fas fa-list-check" aria-hidden="true"></i> Status tracking</span>
</div>
</aside>
</div>

<section class="my-requests" aria-labelledby="my-requests-title">
<div class="requests-head"><div><span class="section-kicker">YOUR HISTORY</span><h2 id="my-requests-title">My prayer requests</h2></div></div>
<div class="requests-list">
@forelse($requests as $prayerRequest)
<article class="card request-card">
<div class="request-top"><div><h3>{{ $prayerRequest->subject }}</h3><p class="muted">Submitted {{ optional($prayerRequest->created_at)->diffForHumans() }}</p></div><span class="status-pill status-{{ str_replace('_', '-', $prayerRequest->status) }}">{{ ucwords(str_replace('_', ' ', $prayerRequest->status)) }}</span></div>
<p>{{ \Illuminate\Support\Str::limit($prayerRequest->message, 260) }}</p>
<div class="request-meta"><span><i class="fas fa-eye" aria-hidden="true"></i> {{ ucwords(str_replace('_', ' ', $prayerRequest->visibility)) }}</span>@if(!empty($prayerRequest->pastoral_notes))<span><i class="fas fa-note-sticky" aria-hidden="true"></i> Pastoral note available</span>@endif</div>
@if(!empty($prayerRequest->pastoral_notes))<div class="pastoral-note"><strong>Pastoral note</strong><p>{{ $prayerRequest->pastoral_notes }}</p></div>@endif
</article>
@empty
<div class="card empty-card"><i class="fas fa-hands-praying" aria-hidden="true"></i><h3>No prayer requests yet</h3><p class="muted">Requests you submit will appear here so you can follow their status.</p></div>
@endforelse
</div>
<div class="pagination">{{ $requests->links() }}</div>
</section>

<style>
.section-kicker{display:inline-block;color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em;margin-bottom:6px}.support-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(280px,.75fr);gap:18px;align-items:start}.section-head{display:flex;gap:14px;align-items:flex-start;margin-bottom:20px}.section-head h2,.pastoral-card h2{margin:0}.section-head p{margin:4px 0 0}.icon-box{width:48px;height:48px;min-width:48px;border-radius:10px;display:grid;place-items:center;background:color-mix(in srgb,var(--primary) 12%,#fff);color:var(--primary);font-size:1.15rem}.prayer-form{display:grid;gap:9px}.prayer-form label{font-weight:800;margin-top:5px}.prayer-form input,.prayer-form textarea,.prayer-form select{width:100%;border:1px solid #d0d5dd;border-radius:10px;padding:12px 13px;background:#fff;color:var(--text);font:inherit}.prayer-form input:focus,.prayer-form textarea:focus,.prayer-form select:focus{outline:0;border-color:var(--primary);box-shadow:0 0 0 4px rgba(75,46,131,.10)}.prayer-form .btn{justify-self:start;margin-top:8px}.privacy-note{display:flex;gap:9px;color:var(--muted);font-size:.88rem;background:var(--surface-soft);border:1px solid var(--border);border-radius:10px;padding:11px 12px}.privacy-note i{color:var(--primary);margin-top:4px}.form-errors{background:#fff1f2;color:#9f1239;border:1px solid #fecdd3;border-radius:10px;padding:12px 14px;margin-bottom:16px}.form-errors ul{margin:7px 0 0;padding-left:20px}.pastoral-card{position:sticky;top:92px}.pastoral-card>.icon-box{margin-bottom:14px}.care-points{display:grid;gap:10px;margin-top:18px}.care-points span{display:flex;gap:9px;align-items:center;font-weight:700}.care-points i{width:18px;color:var(--primary)}.my-requests{margin-top:38px}.requests-head h2{margin:0 0 16px}.requests-list{display:grid;gap:14px}.request-card{padding:18px}.request-top{display:flex;justify-content:space-between;gap:14px;align-items:flex-start}.request-top h3{margin:0}.request-top p{margin:3px 0 0;font-size:.88rem}.status-pill{display:inline-flex;border-radius:999px;padding:5px 10px;background:#eef2ff;color:#3730a3;font-size:.75rem;font-weight:900;white-space:nowrap}.status-resolved,.status-closed{background:#ecfdf3;color:#067647}.status-under-review,.status-referred{background:#fffaeb;color:#b54708}.request-meta{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-size:.88rem;border-top:1px solid var(--border);padding-top:12px}.request-meta span{display:flex;align-items:center;gap:7px}.request-meta i{color:var(--primary)}.pastoral-note{margin-top:14px;background:#f8f5fc;border-left:4px solid var(--primary);border-radius:8px;padding:12px 14px}.pastoral-note p{margin:5px 0 0}.empty-card{text-align:center;padding:34px}.empty-card>i{font-size:2rem;color:var(--primary);margin-bottom:10px}@media(max-width:820px){.support-grid{grid-template-columns:1fr}.pastoral-card{position:static}}@media(max-width:640px){.request-top{flex-direction:column}}
</style>
@endsection