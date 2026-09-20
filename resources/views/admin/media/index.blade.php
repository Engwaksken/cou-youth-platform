@extends('admin.layout')
@section('title','Media Library')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-photo-film"></i> Media Library</h1>
        <p>Manage images, audio, video and document resources for the youth platform.</p>
    </div>
</div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-layer-group"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total media</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ number_format($stats['published']) }}</strong><span>Published</span></div></div>
    <div class="card stat-card stat-blue"><div class="stat-icon"><i class="fas fa-image"></i></div><div><strong>{{ number_format($stats['images']) }}</strong><span>Images</span></div></div>
    <div class="card stat-card stat-orange"><div class="stat-icon"><i class="fas fa-file-lines"></i></div><div><strong>{{ number_format($stats['documents']) }}</strong><span>Documents</span></div></div>
</div>

<div class="card" style="margin-bottom:16px">
    <h3 style="margin-top:0"><i class="fas fa-chart-column"></i> Media by type</h3>
    @php $maxType = max(1, (int) collect($typeCounts)->max()); @endphp
    <div class="mini-chart">
        @foreach(['image'=>'Images','audio'=>'Audio','video'=>'Video','document'=>'Documents'] as $key=>$label)
            @php $count=(int)($typeCounts[$key] ?? 0); $width=($count/$maxType)*100; @endphp
            <div class="chart-row"><span>{{ $label }}</span><div class="chart-track"><div class="chart-bar" style="width:{{ $width }}%"></div></div><strong>{{ $count }}</strong></div>
        @endforeach
    </div>
</div>

<div class="card" style="margin-bottom:16px">
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="form-grid">
        @csrf
        <label>Title<input name="title" required placeholder="Media title"></label>
        <label>Type<select name="type" required><option value="image">Image</option><option value="audio">Audio</option><option value="video">Video</option><option value="document">Document</option></select></label>
        <label class="span-2">Accessible alt text<input name="alt_text" placeholder="Describe the resource for assistive technologies"></label>
        <label class="span-2">File<input type="file" name="file" required></label>
        <label class="checkbox span-2"><input type="checkbox" name="is_published" value="1"> Published</label>
        <div class="span-2"><button class="btn btn-primary" type="submit"><i class="fas fa-cloud-arrow-up"></i> Upload media</button></div>
    </form>
</div>

<form method="get" class="filters">
    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search title, alt text or MIME type">
    <select name="type">
        <option value="">All types</option>
        @foreach(['image'=>'Images','audio'=>'Audio','video'=>'Video','document'=>'Documents'] as $value=>$label)<option value="{{ $value }}" @selected($filters['type']===$value)>{{ $label }}</option>@endforeach
    </select>
    <select name="status"><option value="">All statuses</option><option value="published" @selected($filters['status']==='published')>Published</option><option value="draft" @selected($filters['status']==='draft')>Draft</option></select>
    <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Search</button>
    <a class="btn btn-light" href="{{ route('admin.media.index') }}">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Type</th><th>File</th><th>Status</th><th>Uploaded</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><strong>{{ $item->title }}</strong><small>{{ $item->alt_text ?: 'No alt text provided' }}</small></td>
                    <td>{{ ucfirst($item->type) }}</td>
                    <td>{{ $item->mime_type }}<small>{{ number_format(((int)$item->file_size)/1024,1) }} KB</small></td>
                    <td><span class="badge {{ $item->is_published ? 'badge-success' : 'badge-warning' }}">{{ $item->is_published ? 'Published' : 'Draft' }}</span></td>
                    <td>{{ optional($item->created_at)->format('d M Y') }}</td>
                    <td><form method="post" action="{{ route('admin.media.destroy',$item) }}" onsubmit="return confirm('Delete this media item and its stored file?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Delete</button></form></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No media items found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $items->links() }}</div>
</div>
@endsection

@push('styles')
<style>
    .stat-card{border-left:4px solid var(--primary)}
    .stat-purple{border-left-color:#7c3aed}.stat-green{border-left-color:#16a34a}.stat-blue{border-left-color:#2563eb}.stat-orange{border-left-color:#ea580c}
    .mini-chart{display:grid;gap:12px}.chart-row{display:grid;grid-template-columns:110px 1fr 42px;gap:10px;align-items:center}.chart-track{height:12px;background:#eef2f7;border-radius:99px;overflow:hidden}.chart-bar{height:100%;background:var(--primary);border-radius:99px}.chart-row strong{text-align:right}
</style>
@endpush
