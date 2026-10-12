@extends('admin.layout')
@section('title', 'Users')

@section('content')
@php
    $failedForm = $errors->any() ? old('_user_form') : null;
    $failedUser = (string) old('_user_id');
@endphp

<div class="page-head">
    <div><h1><i class="fas fa-users" aria-hidden="true"></i> Users</h1><p>Manage platform accounts and account details.</p></div>
    <button class="btn btn-primary" type="button" data-user-dialog="create-user"><i class="fas fa-plus" aria-hidden="true"></i> Add user</button>
</div>

<div class="card table-card users-card">
    <div class="users-table-heading"><h2>Platform users</h2><span class="badge">{{ number_format($users->total()) }} total</span></div>
    <div class="table-wrap">
        <table class="users-table">
            <caption class="users-sr-only">Platform accounts and available actions</caption>
            <thead><tr><th scope="col">User</th><th scope="col">Email</th><th scope="col">Joined</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><div class="user-identity"><span class="user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><div><strong>{{ $user->name }}</strong><small>Account #{{ $user->id }}@if($user->id === auth()->id()) · You @endif</small></div></div></td>
                    <td class="user-email">{{ $user->email }}</td>
                    <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                    <td><div class="actions">
                        <button class="btn btn-light" type="button" data-user-dialog="edit-user-{{ $user->id }}" aria-label="Edit {{ $user->name }}"><i class="fas fa-pen" aria-hidden="true"></i> Edit</button>
                        <button class="btn btn-danger" type="button" data-user-dialog="delete-user-{{ $user->id }}" aria-label="Remove {{ $user->name }}"><i class="fas fa-trash" aria-hidden="true"></i> Remove</button>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty"><i class="fas fa-users" aria-hidden="true"></i><p>No users found. Add a user to get started.</p></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination users-pagination">{{ $users->links() }}</div>
</div>

<dialog class="user-dialog" id="create-user" aria-labelledby="create-user-title" @if($failedForm === 'create') data-auto-open @endif>
    <div class="user-dialog-heading"><div><h2 id="create-user-title">Add user</h2><p>Create a new platform account.</p></div><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close add user dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <input type="hidden" name="_user_form" value="create">
        @include('admin.users.partials.form', ['formId' => 'create', 'editingUser' => null, 'restoreInput' => $failedForm === 'create'])
        <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close>Cancel</button><button class="btn btn-primary" type="submit">Create user</button></div>
    </form>
</dialog>

@foreach($users as $user)
    @php($restoreEdit = $failedForm === 'edit' && $failedUser === (string) $user->id)
    <dialog class="user-dialog" id="edit-user-{{ $user->id }}" aria-labelledby="edit-user-title-{{ $user->id }}" @if($restoreEdit) data-auto-open @endif>
        <div class="user-dialog-heading"><div><h2 id="edit-user-title-{{ $user->id }}">Edit user</h2><p>Update {{ $user->name }}’s account details.</p></div><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close edit user dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <input type="hidden" name="_user_form" value="edit"><input type="hidden" name="_user_id" value="{{ $user->id }}">
            @include('admin.users.partials.form', ['formId' => 'edit-'.$user->id, 'editingUser' => $user, 'restoreInput' => $restoreEdit])
            <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close>Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div>
        </form>
    </dialog>
    <dialog class="user-dialog user-dialog-confirm" id="delete-user-{{ $user->id }}" aria-labelledby="delete-user-title-{{ $user->id }}" @if($failedForm === 'delete' && $failedUser === (string) $user->id) data-auto-open @endif>
        <div class="user-dialog-heading"><h2 id="delete-user-title-{{ $user->id }}">Remove user?</h2><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close remove user dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
        <p>Remove <strong>{{ $user->name }}</strong> ({{ $user->email }})? This action cannot be undone.</p>
        @if($failedForm === 'delete' && $failedUser === (string) $user->id)<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
            @csrf @method('DELETE')
            <input type="hidden" name="_user_form" value="delete"><input type="hidden" name="_user_id" value="{{ $user->id }}">
            <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close autofocus>Cancel</button><button class="btn btn-danger" type="submit">Remove user</button></div>
        </form>
    </dialog>
@endforeach
@endsection

@push('styles')
<style>
    .users-table-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px;border-bottom:1px solid var(--border)}.users-table-heading h2{font-size:18px;margin:0}
    .users-table{min-width:620px}.users-table td{vertical-align:middle}.user-identity{display:flex;align-items:center;gap:12px}.user-avatar{display:grid;place-items:center;flex:0 0 40px;height:40px;border-radius:12px;background:var(--primary-light);color:var(--primary);font-weight:800}.user-identity small{display:block;margin-top:4px;color:var(--muted)}.user-email{overflow-wrap:anywhere}.users-pagination{padding:0 18px 18px}
    .user-dialog{width:min(600px,calc(100% - 28px));max-height:calc(100dvh - 32px);padding:24px;border:1px solid var(--border);border-radius:16px;color:var(--text);background:var(--surface);overflow:auto;box-shadow:0 24px 80px rgba(16,24,40,.25)}.user-dialog::backdrop{background:rgba(15,23,42,.6)}.user-dialog-confirm{max-width:460px}.user-dialog-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.user-dialog-heading h2{margin:0;font-size:22px}.user-dialog-heading p,.user-password-help{color:var(--muted);font-size:14px;margin:6px 0 0}.user-dialog .icon-btn{flex-shrink:0}.user-dialog .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:24px}.user-field-error{display:block;color:var(--danger);font-size:13px;margin-top:5px}.users-sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
    @media(max-width:600px){.user-dialog{padding:18px}.user-dialog .form-grid{grid-template-columns:1fr}.users-table .btn{min-height:44px}.user-dialog .modal-actions{flex-wrap:wrap}.user-dialog .modal-actions .btn{flex:1;min-height:44px}}
</style>
@endpush

@push('scripts')
<script>
(() => {
    let previousOverflow = '';
    const dialogs = document.querySelectorAll('.user-dialog');
    const open = dialog => {
        if (!dialog || dialog.open) return;
        previousOverflow = document.body.style.overflow;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
    };
    document.querySelectorAll('[data-user-dialog]').forEach(button => {
        button.addEventListener('click', () => open(document.getElementById(button.dataset.userDialog)));
    });
    dialogs.forEach(dialog => {
        dialog.querySelectorAll('[data-user-dialog-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => { document.body.style.overflow = previousOverflow; });
        dialog.addEventListener('click', event => {
            const bounds = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
        });
    });
    open(document.querySelector('.user-dialog[data-auto-open]'));
})();
</script>
@endpush
