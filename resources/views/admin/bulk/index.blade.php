@extends('admin.layout')
@section('title','Bulk SMS / Email')
@section('content')
<div class="page-head">
    <div><h1><i class="fas fa-paper-plane"></i> Bulk SMS / Email</h1><p>Send communication to all users or a selected department.</p></div>
    <button class="btn btn-primary" data-open-modal="bulk-message-create"><i class="fas fa-plus"></i> New Message</button>
</div>

<div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Channel</th><th>Subject</th><th>Audience</th><th>Recipients</th><th>Status</th><th>Sent</th></tr></thead><tbody>
@forelse($items as $m)
<tr><td><span class="badge">{{ strtoupper($m->channel) }}</span></td><td><strong>{{ $m->subject ?: 'No subject' }}</strong><small>{{ \Str::limit($m->body,100) }}</small></td><td>{{ $m->audience }}</td><td>{{ number_format((int)$m->recipient_count) }}</td><td><span class="badge {{ $m->status==='sent'?'badge-success':($m->status==='partial'?'badge-warning':'') }}">{{ ucfirst($m->status) }}</span></td><td>{{ optional($m->created_at)->format('d M Y H:i') }}</td></tr>
@empty<tr><td colspan="6" class="empty">No bulk messages have been sent yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $items->links() }}</div></div>

<div id="bulk-message-create" class="modal"><div class="modal-card"><div class="modal-head"><h2>Send Bulk Message</h2><button class="icon-btn" type="button" data-close-modal><i class="fas fa-xmark"></i></button></div>
<form method="POST" action="{{ route('admin.bulk.store') }}">@csrf
<div class="form-grid">
<label>Channel<select name="channel" id="bulkChannel" required><option value="email">Email</option><option value="sms">SMS</option></select></label>
<label>Recipients<select name="audience_type" id="bulkAudienceType" required><option value="all">Select all users</option><option value="department">Select by department</option></select></label>
<label class="span-2" id="departmentField" style="display:none">Department / Church Unit<select name="organisation_unit_id" id="bulkDepartment"><option value="">Choose department</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}{{ $department->type ? ' · '.ucwords(str_replace('_',' ',$department->type)) : '' }}</option>@endforeach</select></label>
<label class="span-2" id="subjectField">Email subject<input name="subject" maxlength="180" placeholder="e.g. Youth camp reminder"></label>
<label class="span-2">Message<textarea name="body" rows="7" maxlength="5000" placeholder="Type your message here..." required></textarea></label>
</div>
<div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Send Message</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
(() => {
 const audience=document.getElementById('bulkAudienceType');
 const departmentField=document.getElementById('departmentField');
 const department=document.getElementById('bulkDepartment');
 const channel=document.getElementById('bulkChannel');
 const subjectField=document.getElementById('subjectField');
 const syncAudience=()=>{const show=audience?.value==='department'; if(departmentField) departmentField.style.display=show?'':'none'; if(department) department.required=show;};
 const syncChannel=()=>{if(subjectField) subjectField.style.display=channel?.value==='email'?'':'none';};
 audience?.addEventListener('change',syncAudience); channel?.addEventListener('change',syncChannel); syncAudience(); syncChannel();
})();
</script>
@endpush
