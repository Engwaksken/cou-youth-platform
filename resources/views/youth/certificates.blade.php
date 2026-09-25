@extends('public.layout')
@section('title', 'My Certificates | COU Youth')
@section('content')
<section class="hero"><span class="section-kicker">ACHIEVEMENTS</span><h1>My certificates</h1><p>View certificates issued after completing eligible discipleship and learning courses.</p></section>
<section class="certificate-section">
<div class="certificate-grid">
@forelse($certificates as $certificate)
<article class="card certificate-card">
<div class="certificate-icon"><i class="fas fa-award"></i></div>
<div><span class="section-kicker">CERTIFICATE</span><h2>{{ $certificate->course?->title ?? 'Course certificate' }}</h2></div>
<dl><div><dt>Certificate number</dt><dd>{{ $certificate->certificate_number }}</dd></div><div><dt>Issued</dt><dd>{{ $certificate->issued_at?->format('d M Y') ?? $certificate->created_at?->format('d M Y') }}</dd></div></dl>
@if($certificate->course)<a class="btn" href="{{ route('public.courses.show',$certificate->course) }}">View course</a>@endif
</article>
@empty
<div class="card empty-card"><div class="certificate-icon"><i class="fas fa-award"></i></div><h2>No certificates yet</h2><p class="muted">Complete all published lessons in an enrolled course to earn a certificate where available.</p><a class="btn btn-primary" href="{{ route('youth.learning') }}">Continue learning</a></div>
@endforelse
</div>
<div class="pagination">{{ $certificates->links() }}</div>
</section>
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.certificate-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.certificate-card{display:flex;flex-direction:column;gap:14px}.certificate-card h2{font-size:1.15rem;margin:2px 0 0}.certificate-icon{width:50px;height:50px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center;font-size:1.25rem}.certificate-card dl{display:grid;gap:9px;margin:0}.certificate-card dl div{padding:10px;border:1px solid var(--border);border-radius:9px;background:var(--surface-soft)}.certificate-card dt{font-size:.74rem;color:var(--muted);font-weight:800}.certificate-card dd{margin:3px 0 0;font-weight:800}.certificate-card .btn{margin-top:auto}.empty-card{grid-column:1/-1;text-align:center;padding:34px}.empty-card .certificate-icon{margin:0 auto}@media(max-width:900px){.certificate-grid{grid-template-columns:1fr 1fr}}@media(max-width:620px){.certificate-grid{grid-template-columns:1fr}}
</style>
@endsection
