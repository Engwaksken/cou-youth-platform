@extends('admin.layout')
@section('title', 'Role assignments')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-user-shield"></i> Organisation role assignments</h1><p>Assign an account a CMS role or a named organisation role.</p></div></div>
@php
    $cmsRoles = \App\Models\User::CMS_ROLES;
    $createOrganisationId = old('organisation_unit_id', '');
@endphp
<div class="card">
    <h2>Assign role</h2>
    <form method="post" action="{{ route('admin.user-organisation-roles.store') }}">
        @csrf
        <div class="form-grid">
            <label>User
                <select name="user_id" required>
                    <option value="">Choose user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                    @endforeach
                </select>
            </label>
            <label for="create-organisation">Organisation
                <select id="create-organisation" name="organisation_unit_id" data-role-scope>
                    <option value="">Global / no organisation</option>
                    @foreach($organisationUnits as $unit)
                        <option value="{{ $unit->id }}" @selected((string) $createOrganisationId === (string) $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </label>
            <label for="create-role">Role
                <select id="create-role" name="role" required data-scoped-roles>
                    @foreach($cmsRoles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" data-organisation-id="{{ $role->organisation_unit_id ?? '' }}" @selected(old('role') === $role->name)>{{ $role->name }} — {{ $role->organisationUnit?->name ?? 'Global' }}</option>
                    @endforeach
                </select>
            </label>
            <span class="sr-only" data-role-status aria-live="polite"></span>
            <label class="checkbox"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        </div>
        <button class="btn btn-primary">Create assignment</button>
    </form>
</div>
<div class="card table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>User</th><th>Role / organisation</th><th>Update</th><th>Remove</th></tr></thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->user?->name }}<small>{{ $assignment->user?->email }}</small></td>
                        <td>{{ $assignment->role }}<small>{{ $assignment->organisationUnit?->name ?? 'Global' }}</small></td>
                        <td>
                            <form method="post" action="{{ route('admin.user-organisation-roles.update', $assignment) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <label for="assignment-{{ $assignment->id }}-organisation">Organisation
                                        <select id="assignment-{{ $assignment->id }}-organisation" name="organisation_unit_id" data-role-scope>
                                            <option value="">Global / no organisation</option>
                                            @foreach($organisationUnits as $unit)
                                                <option value="{{ $unit->id }}" @selected($assignment->organisation_unit_id == $unit->id)>{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label for="assignment-{{ $assignment->id }}-role">Role
                                        <select id="assignment-{{ $assignment->id }}-role" name="role" required data-scoped-roles>
                                            @foreach($cmsRoles as $role)
                                                <option value="{{ $role }}" @selected($assignment->role === $role)>{{ $role }}</option>
                                            @endforeach
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" data-organisation-id="{{ $role->organisation_unit_id ?? '' }}" @selected($assignment->role === $role->name)>{{ $role->name }} — {{ $role->organisationUnit?->name ?? 'Global' }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <span class="sr-only" data-role-status aria-live="polite"></span>
                                    <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked($assignment->is_active)> Active</label>
                                </div>
                                <button class="btn btn-primary">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.user-organisation-roles.destroy', $assignment) }}" onsubmit="return confirm('Remove this role assignment?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">No role assignments.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $assignments->links() }}</div>
</div>
<script>
    document.querySelectorAll('[data-role-scope]').forEach(function (scope) {
        const form = scope.closest('form');
        const roleSelect = form.querySelector('[data-scoped-roles]');
        const status = form.querySelector('[data-role-status]');
        const initiallySelectedRole = roleSelect.options[roleSelect.selectedIndex];
        const initialRoleValue = initiallySelectedRole ? initiallySelectedRole.value : null;
        let initialRender = true;

        function filterRoles() {
            const selectedScope = scope.value;
            let visibleNamedRoles = 0;
            let selectedRoleIsValid = true;
            let firstCmsRole = null;
            Array.from(roleSelect.options).forEach(function (option) {
                if (!option.hasAttribute('data-organisation-id')) {
                    if (!firstCmsRole) firstCmsRole = option;
                    return;
                }
                const roleScope = option.dataset.organisationId;
                const matches = roleScope === '' || roleScope === selectedScope;
                const preserveInitialAssignment = initialRender && option.value === initialRoleValue;
                option.hidden = !matches && !preserveInitialAssignment;
                option.disabled = !matches && !preserveInitialAssignment;
                if (!option.hidden) visibleNamedRoles++;
                if (option.selected && !matches && !preserveInitialAssignment) selectedRoleIsValid = false;
            });
            if (!initialRender && !selectedRoleIsValid && firstCmsRole) {
                roleSelect.value = firstCmsRole.value;
            }
            if (status) status.textContent = visibleNamedRoles
                ? visibleNamedRoles + ' organisation role' + (visibleNamedRoles === 1 ? '' : 's') + ' available for this scope.'
                : 'No organisation-specific roles available for this scope.';
        }

        scope.addEventListener('change', filterRoles);
        filterRoles();
        initialRender = false;
    });
</script>
@endsection
