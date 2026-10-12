@extends('admin.layout')
@section('title', 'Role assignments')

@section('content')
@php
    $cmsRoles = \App\Models\User::CMS_ROLES;
    $failedForm = $errors->any() ? old('_assignment_form') : null;
    $failedAssignment = (string) old('_assignment_id');
@endphp
<div class="page-head">
    <div><h1><i class="fas fa-user-shield" aria-hidden="true"></i> Organisation role assignments</h1><p>Assign an account a CMS role or a named organisation role.</p></div>
    <button class="btn btn-primary" type="button" data-user-dialog="create-assignment"><i class="fas fa-plus" aria-hidden="true"></i> Assign role</button>
</div>

<div class="card table-card">
    <div class="users-table-heading"><h2>Role assignments</h2><span class="badge">{{ number_format($assignments->total()) }} total</span></div>
    <div class="table-wrap">
        <table class="users-table assignments-table">
            <caption class="users-sr-only">Organisation role assignments and available actions</caption>
            <thead><tr><th scope="col">User</th><th scope="col">Role</th><th scope="col">Organisation</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td><div class="user-identity"><span class="user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($assignment->user?->name ?? '?', 0, 1)) }}</span><div><strong>{{ $assignment->user?->name ?? 'Unknown user' }}</strong><small>{{ $assignment->user?->email }}</small></div></div></td>
                    <td><strong>{{ $assignment->role }}</strong></td>
                    <td>{{ $assignment->organisationUnit?->name ?? 'Global' }}</td>
                    <td><span class="assignment-status {{ $assignment->is_active ? 'is-active' : 'is-inactive' }}">{{ $assignment->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><div class="actions">
                        <button class="btn btn-light" type="button" data-user-dialog="edit-assignment-{{ $assignment->id }}" aria-label="Edit role assignment for {{ $assignment->user?->name }}"><i class="fas fa-pen" aria-hidden="true"></i> Edit</button>
                        <button class="btn btn-danger" type="button" data-user-dialog="delete-assignment-{{ $assignment->id }}" aria-label="Remove role assignment for {{ $assignment->user?->name }}"><i class="fas fa-trash" aria-hidden="true"></i> Remove</button>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty"><i class="fas fa-user-shield" aria-hidden="true"></i><p>No role assignments. Assign a role to get started.</p></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination users-pagination">{{ $assignments->links() }}</div>
</div>

<dialog class="user-dialog" id="create-assignment" aria-labelledby="create-assignment-title" @if($failedForm === 'create') data-auto-open @endif>
    <div class="user-dialog-heading"><div><h2 id="create-assignment-title">Assign role</h2><p>Select an account, organisation scope, and role.</p></div><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close assign role dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
    <form method="POST" action="{{ route('admin.user-organisation-roles.store') }}">
        @csrf
        <input type="hidden" name="_assignment_form" value="create">
        @include('admin.user-organisation-roles.partials.form', ['formId' => 'create', 'editingAssignment' => null, 'restoreInput' => $failedForm === 'create'])
        <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close>Cancel</button><button class="btn btn-primary" type="submit">Create assignment</button></div>
    </form>
</dialog>

@foreach($assignments as $assignment)
    @php($restoreEdit = $failedForm === 'edit' && $failedAssignment === (string) $assignment->id)
    <dialog class="user-dialog" id="edit-assignment-{{ $assignment->id }}" aria-labelledby="edit-assignment-title-{{ $assignment->id }}" @if($restoreEdit) data-auto-open @endif>
        <div class="user-dialog-heading"><div><h2 id="edit-assignment-title-{{ $assignment->id }}">Edit role assignment</h2><p>{{ $assignment->user?->name }} — {{ $assignment->user?->email }}</p></div><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close edit assignment dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
        <form method="POST" action="{{ route('admin.user-organisation-roles.update', $assignment) }}">
            @csrf @method('PUT')
            <input type="hidden" name="_assignment_form" value="edit"><input type="hidden" name="_assignment_id" value="{{ $assignment->id }}">
            @include('admin.user-organisation-roles.partials.form', ['formId' => 'assignment-'.$assignment->id, 'editingAssignment' => $assignment, 'restoreInput' => $restoreEdit])
            <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close>Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div>
        </form>
    </dialog>
    <dialog class="user-dialog user-dialog-confirm" id="delete-assignment-{{ $assignment->id }}" aria-labelledby="delete-assignment-title-{{ $assignment->id }}" @if($failedForm === 'delete' && $failedAssignment === (string) $assignment->id) data-auto-open @endif>
        <div class="user-dialog-heading"><h2 id="delete-assignment-title-{{ $assignment->id }}">Remove assignment?</h2><button class="icon-btn" type="button" data-user-dialog-close aria-label="Close remove assignment dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
        <p>Remove <strong>{{ $assignment->role }}</strong> from <strong>{{ $assignment->user?->name }}</strong> in {{ $assignment->organisationUnit?->name ?? 'Global' }}? The user account will not be deleted.</p>
        @if($failedForm === 'delete' && $failedAssignment === (string) $assignment->id)<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.user-organisation-roles.destroy', $assignment) }}">
            @csrf @method('DELETE')
            <input type="hidden" name="_assignment_form" value="delete"><input type="hidden" name="_assignment_id" value="{{ $assignment->id }}">
            <div class="modal-actions"><button class="btn btn-light" type="button" data-user-dialog-close autofocus>Cancel</button><button class="btn btn-danger" type="submit">Remove assignment</button></div>
        </form>
    </dialog>
@endforeach
@endsection

@include('admin.partials.account-modals')

@push('styles')
<style>
    .assignments-table{min-width:760px}.assignment-status{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:800}.assignment-status.is-active{background:#ecfdf5;color:#166534}.assignment-status.is-inactive{background:#f2f4f7;color:#475467}.assignment-active{display:flex;align-items:center;gap:8px;margin-top:16px}.assignment-active input{width:18px;height:18px;accent-color:var(--primary)}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-role-scope]').forEach(scope => {
    const form = scope.closest('form');
    const roleSelect = form.querySelector('[data-scoped-roles]');
    const status = form.querySelector('[data-role-status]');
    const initialRoleValue = roleSelect.value;
    let initialRender = true;
    const filterRoles = () => {
        let visibleNamedRoles = 0;
        let selectedRoleIsValid = true;
        Array.from(roleSelect.options).forEach(option => {
            if (!option.hasAttribute('data-organisation-id')) return;
            const matches = option.dataset.organisationId === '' || option.dataset.organisationId === scope.value;
            const preserveInitialAssignment = initialRender && roleSelect.hasAttribute('data-preserve-assignment') && option.value === initialRoleValue;
            option.hidden = !matches && !preserveInitialAssignment;
            option.disabled = !matches && !preserveInitialAssignment;
            if (!option.hidden) visibleNamedRoles++;
            if (option.selected && option.disabled) selectedRoleIsValid = false;
        });
        if (!selectedRoleIsValid) roleSelect.value = '';
        if (status) status.textContent = visibleNamedRoles
            ? `${visibleNamedRoles} organisation role${visibleNamedRoles === 1 ? '' : 's'} available for this scope.`
            : 'No organisation-specific roles available for this scope.';
    };
    scope.addEventListener('change', filterRoles);
    filterRoles();
    initialRender = false;
});
</script>
@endpush
