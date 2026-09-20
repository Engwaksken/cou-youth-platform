@extends('admin.layout')

@section('title', 'Content')

@section('content')
    @php
        $contentTypes = [
            'news' => 'News',
            'announcement' => 'Announcement',
            'devotion' => 'Devotion',
            'bible_study' => 'Bible Study',
            'resource' => 'Resource',
            'opportunity' => 'Opportunity',
            'mission' => 'Mission & Evangelism',
            'talent' => 'Talent Hub',
            'youth_business' => 'Youth Business Directory',
        ];
    @endphp

    <div class="page-header">
        <div>
            <h1>Content</h1>
            <p class="muted">Publish youth ministry updates, discipleship material, missions, opportunities, talent profiles and youth businesses.</p>
        </div>
    </div>

    <div class="card">
        <form method="post" action="{{ route('admin.content.store') }}" class="form-grid">
            @csrf

            <div class="form-group">
                <label for="content-title">Title</label>
                <input id="content-title" name="title" value="{{ old('title') }}" placeholder="Content title" required>
            </div>

            <div class="form-group">
                <label for="content-type">Content type</label>
                <select id="content-type" name="type" required>
                    @foreach($contentTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="content-status">Status</label>
                <select id="content-status" name="status" required>
                    @foreach(['draft' => 'Draft', 'pending' => 'Pending review', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group form-group-full">
                <label for="content-summary">Summary</label>
                <textarea id="content-summary" name="summary" rows="3" maxlength="500" placeholder="Short summary for cards and mobile discovery">{{ old('summary') }}</textarea>
            </div>

            <div class="form-group form-group-full">
                <label for="content-body">Content</label>
                <textarea id="content-body" name="body" rows="10" placeholder="Write the full content here" required>{{ old('body') }}</textarea>
            </div>

            <div class="form-group form-group-full">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Save content
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <form method="get" class="toolbar" style="margin-bottom: 1rem;">
            <select name="type" aria-label="Filter by content type">
                <option value="">All content types</option>
                @foreach($contentTypes as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Filter
            </button>
        </form>

        @forelse($items as $item)
            <div class="list-row" style="padding: .85rem 0; border-bottom: 1px solid #e5e7eb;">
                <strong>{{ $item->title }}</strong>
                <span class="muted"> · {{ $contentTypes[$item->type] ?? ucfirst(str_replace('_', ' ', $item->type)) }} · {{ ucfirst($item->status) }}</span>
            </div>
        @empty
            <p class="muted">No content has been created yet.</p>
        @endforelse

        <div style="margin-top: 1rem;">
            {{ $items->links() }}
        </div>
    </div>
@endsection
