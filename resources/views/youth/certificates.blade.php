@extends('youth.layout')
@section('title', 'My Certificates | COU Youth')
@section('youth_content')
<section class="hero">
    <h1>Things you've <span class="scribble">finished</span></h1>
    <p>Every certificate here is a course you saw through to the end. That's worth celebrating.</p>
</section>

<div class="certificate-grid">
    @forelse($certificates as $certificate)
        <article class="certificate">
            <span class="seal" aria-hidden="true"><i class="fas fa-award"></i></span>
            <span class="cert-label">Certificate of completion</span>
            <h2>{{ $certificate->course?->title ?? 'Course certificate' }}</h2>
            <p class="cert-date">{{ ($certificate->issued_at ?? $certificate->created_at)?->format('j F Y') }}</p>
            <p class="cert-no">No. {{ $certificate->certificate_number }}</p>
            @if($certificate->course)<a class="btn btn-ghost" href="{{ route('public.courses.show', $certificate->course) }}">Revisit the course <i class="fas fa-arrow-right"></i></a>@endif
        </article>
    @empty
        <div class="card empty-card">
            <span class="empty-emoji" aria-hidden="true"><i class="fas fa-medal"></i></span>
            <h3>Your first one is closer than you think</h3>
            <p class="muted">Finish every lesson in a course and your certificate shows up here, ready to share.</p>
            <a class="btn btn-primary" href="{{ route('youth.learning') }}">Back to my courses</a>
        </div>
    @endforelse
</div>
<div class="pagination">{{ $certificates->links() }}</div>

<style>
.certificate-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
.certificate{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;gap:4px;padding:22px 18px 18px;background:#fffdf7;border:var(--bw) solid var(--edge);border-radius:var(--r-card);box-shadow:inset 0 0 0 7px #fffdf7,inset 0 0 0 8.5px var(--sun)}
.seal{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;background:var(--sun);color:var(--ink);font-size:1.6rem;border:var(--bw) solid var(--edge);margin-bottom:10px;transform:rotate(calc(-8deg * var(--tilt)))}
.cert-label{font-size:.78rem;font-weight:800;color:var(--muted);letter-spacing:.04em}
.certificate h2{font-size:1.1rem;margin:4px 0 2px}
.cert-date{margin:0;font-weight:700;color:var(--primary)}
.cert-no{margin:0 0 10px;font-size:.8rem;color:var(--muted);font-variant-numeric:tabular-nums}
.certificate .btn{margin-top:auto}
.empty-card{grid-column:1/-1}
@media(max-width:1000px){.certificate-grid{grid-template-columns:1fr 1fr}}
@media(max-width:620px){.certificate-grid{grid-template-columns:1fr}}
</style>
@endsection
