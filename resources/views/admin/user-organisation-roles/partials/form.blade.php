@php
    $selectedScope = $restoreInput ? old('organisation_unit_id', '') : ($editingAssignment?->organisation_unit_id ?? '');
    $selectedRole = $restoreInput ? old('role', '') : ($editingAssignment?->role ?? '');
    $active = $restoreInput ? (bool) old('is_active', false) : ($editingAssignment?->is_active ?? true);
@endphp
@if($restoreInput && $errors->any())
    <div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
@endif
<div class="form-grid">
    @if(!$editingAssignment)
        <label for="{{ $formId }}-user">User
            <select id="{{ $formId }}-user" name="user_id" required autofocus>
                <option value="">Choose user</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected($restoreInput && (string) old('user_id') === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                @endforeach
            </select>
        </label>
    @endif
    <label for="{{ $formId }}-organisation">Organisation
        <select id="{{ $formId }}-organisation" name="organisation_unit_id" data-role-scope @if($editingAssignment) autofocus @endif>
            <option value="">Global / no organisation</option>
            @foreach($organisationUnits as $unit)
                <option value="{{ $unit->id }}" @selected((string) $selectedScope === (string) $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </label>
    <label for="{{ $formId }}-role">Role
        <select id="{{ $formId }}-role" name="role" required data-scoped-roles @if($editingAssignment && !$restoreInput) data-preserve-assignment @endif aria-describedby="{{ $formId }}-role-status">
            <option value="">Choose role</option>
            @foreach($cmsRoles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>
            @endforeach
            @foreach($roles as $role)
                <option value="{{ $role->name }}" data-organisation-id="{{ $role->organisation_unit_id ?? '' }}" @selected($selectedRole === $role->name && ($role->organisation_unit_id === null || (string) $selectedScope === (string) $role->organisation_unit_id))>{{ $role->name }} — {{ $role->organisationUnit?->name ?? 'Global' }}</option>
            @endforeach
            @if($editingAssignment && !$restoreInput && !in_array($selectedRole, $cmsRoles, true) && !$roles->contains('name', $selectedRole))
                <option value="{{ $selectedRole }}" data-organisation-id="{{ $editingAssignment->organisation_unit_id ?? '' }}" selected>{{ $selectedRole }} — Current assignment</option>
            @endif
        </select>
        <span class="users-sr-only" id="{{ $formId }}-role-status" data-role-status aria-live="polite"></span>
    </label>
</div>
<input type="hidden" name="is_active" value="0">
<label class="assignment-active" for="{{ $formId }}-active"><input id="{{ $formId }}-active" type="checkbox" name="is_active" value="1" @checked($active)> Active assignment</label>
