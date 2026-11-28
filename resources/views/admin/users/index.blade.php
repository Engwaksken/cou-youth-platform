@extends('admin.layout')
@section('title', 'Users')

@section('content')
@php
    $failedForm = $errors->any() ? old('_user_form') : null;
    $failedUser = (string) old('_user_id');
@endphp

<div class="page-head">
    <div><h1><i class="fas fa-users" aria-hidden="true"></i> Users</h1><p>Manage platform accounts and account details.</p></div>
    <div class="actions">
        @php($exportQuery = $dioceseId > 0 ? ['diocese_id' => $dioceseId] : [])
        <a class="btn btn-light" href="{{ route('admin.users.export.csv', $exportQuery) }}"><i class="fas fa-file-csv" aria-hidden="true"></i> Export CSV</a>
        <a class="btn btn-light" href="{{ route('admin.users.export.pdf', $exportQuery) }}"><i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF</a>
        <button class="btn btn-primary" type="button" data-user-dialog="create-user"><i class="fas fa-plus" aria-hidden="true"></i> Add user</button>
    </div>
</div>

<div class="grid stats-grid">
    <div class="card stat-card"><div class="stat-icon"><i class="fas fa-users" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total users</span></div></div>
    <div class="card stat-card"><div class="stat-icon"><i class="fas fa-user-shield" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['staff']) }}</strong><span>Admin staff</span></div></div>
    <div class="card stat-card"><div class="stat-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['youth']) }}</strong><span>Youth members</span></div></div>
    <div class="card stat-card"><div class="stat-icon"><i class="fas fa-user-plus" aria-hidden="true"></i></div><div><strong>{{ number_format($stats['new']) }}</strong><span>Joined in last 30 days</span></div></div>
</div>

<div class="card"><form method="GET" class="filters" style="grid-template-columns:2fr auto auto"><select name="diocese_id" aria-label="Filter by diocese"><option value="">All dioceses</option>@foreach($dioceses as $diocese)<option value="{{ $diocese->id }}" @selected($dioceseId === $diocese->id)>{{ $diocese->name }}</option>@endforeach</select><button class="btn btn-primary"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button><a class="btn btn-light" href="{{ route('admin.users.index') }}">Reset</a></form></div>

<div class="card table-card users-card">
    <div class="users-table-heading"><h2>Platform users</h2><span class="badge">{{ number_format($users->total()) }} total</span></div>
    <div class="table-wrap">
        <table class="users-table">
            <caption class="users-sr-only">Platform accounts and available actions</caption>
            <thead><tr><th scope="col">User</th><th scope="col">Contact</th><th scope="col">Diocese</th><th scope="col">Joined</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><div class="user-identity">@if($user->photoUrl())<img class="user-avatar user-avatar-photo" src="{{ $user->photoUrl() }}" alt="" loading="lazy">@else<span class="user-avatar" aria-hidden="true">{{ $user->initials() }}</span>@endif<div><a class="user-profile-link" href="{{ route('admin.users.show', $user) }}"><strong>{{ $user->name }}</strong></a><small>Account #{{ $user->id }}@if($user->id === auth()->id()) · You @endif</small></div></div></td>
                    <td class="user-email">{{ $user->email }}@if($user->phone)<small>{{ $user->phone }}</small>@endif @if($user->location)<small>{{ $user->location }}</small>@endif</td>
                    <td>{{ $user->diocese?->name ?? $user->youthProfile?->diocese?->name ?? '—' }}</td>
                    <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                    <td><div class="actions">
                        <a class="icon-btn" href="{{ route('admin.users.show', $user) }}" title="View profile" aria-label="View {{ $user->name }}’s profile"><i class="fas fa-id-badge" aria-hidden="true"></i></a>
                        <button class="icon-btn" type="button" data-user-dialog="edit-user-{{ $user->id }}" title="Edit user" aria-label="Edit {{ $user->name }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                        <button class="icon-btn icon-btn-danger" type="button" data-user-dialog="delete-user-{{ $user->id }}" title="Remove user" aria-label="Remove {{ $user->name }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty"><i class="fas fa-users" aria-hidden="true"></i><p>No users found. Add a user to get started.</p></td></tr>
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

@include('admin.partials.account-modals')
