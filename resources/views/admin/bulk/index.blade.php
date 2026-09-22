@extends('admin.layout')
@section('title','Bulk SMS / Email')
@section('content')
<div class="page-head"><h1><i class="fas fa-paper-plane"></i> Bulk SMS / Email</h1><p>Broadcast to users. SMS uses Africa's Talking if configured.</p></div>
<div class="card"><form method="POST" action="{{ route('admin.bulk.store') }}"><div class="form-grid">@csrf
<label>Channel<select name="channel"><option value="sms">sms</option><option value="email">email</option></select></label>
<label>Audience<input name="audience" placeholder="e.g. all, teens, volunteers" value="all"></label>
<label class="span-2">Subject<input name="subject" placeholder="e.g. Youth camp reminder (email subject)"></label>
<label class="span-2">Body<textarea name="body" placeholder="Type your message here..." rows="4" required></textarea></label>
</div><div class="modal-actions"><button class="btn btn-primary" type="submit">Send</button></div></form></div>
<div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Channel</th><th>Subject</th><th>Audience</th><th>Recipients</th><th>Status</th><th>Sent</th></tr></thead><tbody>
@foreach($items as $m)<tr><td><span class="badge">{{ $m->channel }}</span></td><td>{{ $m->subject }}<small>{{ \Str::limit($m->body,100) }}</small></td><td>{{ $m->audience }}</td><td>{{ $m->recipient_count }}</td><td>{{ $m->status }}</td><td>{{ $m->created_at }}</td></tr>@endforeach
</tbody></table></div><div class="pagination">{{ $items->links() }}</div></div>
@endsection
