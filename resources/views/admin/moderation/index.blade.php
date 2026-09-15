@extends('admin.layout')
@section('content')
<div class="container-fluid"><h1>Moderation & Safety Reports</h1>
<form class="mb-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['open','reviewing','resolved','dismissed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></form>
@foreach($reports as $report)<div class="card mb-3"><div class="card-body"><div class="d-flex justify-content-between"><strong>{{ $report->reason }}</strong><span class="badge bg-secondary">{{ $report->status }}</span></div><p class="mb-1">{{ $report->details }}</p><small>Reported by {{ optional($report->reporter)->name ?? 'User' }} · {{ $report->created_at->format('d M Y H:i') }}</small>
<form method="POST" action="{{ route('admin.moderation.resolve',$report) }}" class="row g-2 mt-3">@csrf @method('PUT')<div class="col-md-3"><select class="form-select" name="status"><option value="reviewing">Reviewing</option><option value="resolved">Resolved</option><option value="dismissed">Dismissed</option></select></div><div class="col-md-7"><input class="form-control" name="resolution_notes" placeholder="Resolution notes"></div><div class="col-md-2"><button class="btn btn-primary w-100">Update</button></div></form>
</div></div>@endforeach
{{ $reports->links() }}
</div>
@endsection
