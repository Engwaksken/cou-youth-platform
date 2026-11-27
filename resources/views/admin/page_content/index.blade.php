@extends('admin.layout')
@section('title','Page Content')
@section('content')
@php($pageLabel = $pages[$page]['label'])
<div class="page-head">
    <div>
        <h1><i class="fas fa-pager"></i> Page Content</h1>
        <p>Edit the wording, slides and cards on the public pages. Editing: <strong>{{ $pageLabel }}</strong></p>
    </div>
    <div class="actions">
        @if(\Illuminate\Support\Facades\Route::has($pages[$page]['route']))
            <a class="btn btn-light" href="{{ route($pages[$page]['route']) }}" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> View page</a>
        @endif
        @if($hasMedia)
            <button type="button" class="btn btn-primary" data-open-modal="addSlideModal"><i class="fas fa-plus"></i> Add Slide</button>
            <button type="button" class="btn btn-primary" data-open-modal="addCardModal"><i class="fas fa-plus"></i> Add Card</button>
        @endif
    </div>
</div>

<form method="GET" action="{{ route('admin.page-content.index') }}" class="card">
    <div class="filters" style="grid-template-columns:2fr auto">
        <select name="page" aria-label="Page to edit" onchange="this.form.submit()">
            @foreach($pages as $key => $definition)
                <option value="{{ $key }}" @selected($page === $key)>{{ $definition['label'] }}</option>
            @endforeach
        </select>
        <button class="btn btn-light" type="submit">Open</button>
    </div>
</form>

<div data-tabs>
<div class="tabs">
    @if($canEditText)<button class="tab active" type="button" data-tab-target="tabText"><i class="fas fa-font"></i> Page text <span class="tab-count">{{ $customisedCount }} edited</span></button>@endif
    @if($hasMedia)
        <button class="tab @if(!$canEditText) active @endif" type="button" data-tab-target="tabSlides">Slides <span class="tab-count">{{ $slides->count() }}</span></button>
        <button class="tab" type="button" data-tab-target="tabCards">Cards <span class="tab-count">{{ $cards->count() }}</span></button>
    @endif
</div>

@if($canEditText)
<div class="tab-panel active" id="tabText">
    <form method="POST" action="{{ route('admin.page-content.texts.update', $page) }}" class="page-text-form">
        @csrf @method('PUT')
        <p class="muted page-text-help"><i class="fas fa-circle-info"></i> Change any wording below and save. Leave a box empty, or tick <em>Reset</em>, to go back to the original text.</p>
        @foreach($textFields as $group => $fields)
            <section class="card page-text-group">
                <h2>{{ $group }}</h2>
                <div class="form-grid">
                    @foreach($fields as $field)
                        <div class="page-text-field @if($field['type'] === 'textarea') span-2 @endif">
                            <label for="text-{{ $field['key'] }}">
                                {{ $field['label'] }}
                                @if($field['customised'])<span class="badge badge-warning">Edited</span>@endif
                            </label>
                            @if($field['type'] === 'textarea')
                                <textarea id="text-{{ $field['key'] }}" name="texts[{{ $field['key'] }}]" rows="3" maxlength="2000">{{ old('texts.'.$field['key'], $field['value']) }}</textarea>
                            @else
                                <input id="text-{{ $field['key'] }}" name="texts[{{ $field['key'] }}]" maxlength="255" value="{{ old('texts.'.$field['key'], $field['value']) }}">
                            @endif
                            @if($field['customised'])
                                <div class="page-text-meta">
                                    <small class="muted">Original: “{{ \Illuminate\Support\Str::limit($field['default'], 120) }}”</small>
                                    <label class="checkbox"><input type="checkbox" name="reset[{{ $field['key'] }}]" value="1"> Reset</label>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
        <div class="page-text-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save {{ $pageLabel }} text</button></div>
    </form>
</div>
@endif

@if($hasMedia)
<div class="tab-panel @if(!$canEditText) active @endif" id="tabSlides"><div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Media</th><th>Title</th><th>Link</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($slides as $s)<tr><td>@if($s->media_path)@if($s->media_type==='video')<video src="{{ asset('storage/'.$s->media_path) }}" width="120" controls></video>@else<img src="{{ asset('storage/'.$s->media_path) }}" width="120" alt="">@endif @else — @endif</td><td>{{ $s->title }}<small>{{ $s->subtitle }}</small></td><td>{{ $s->link_url }}</td><td>{{ $s->sort_order }}</td><td><span class="badge {{ $s->is_active?'badge-success':'badge-warning' }}">{{ $s->is_active?'Active':'Hidden' }}</span></td><td><div class="actions"><button class="btn btn-light" type="button" data-open-modal="editSlide{{ $s->id }}"><i class="fas fa-pen"></i> Edit</button><form method="POST" action="{{ route('admin.page-content.slides.destroy',$s) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form></div></td></tr>
@empty<tr><td colspan="6" class="empty">No slides on this page yet.</td></tr>@endforelse
</tbody></table></div></div></div>
<div class="tab-panel" id="tabCards"><div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Icon/Image</th><th>Title</th><th>Link</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($cards as $c)<tr><td><span class="card-icon-preview"><i class="fas {{ $c->icon ?? 'fa-star' }}"></i></span>@if($c->image_path)<img src="{{ asset('storage/'.$c->image_path) }}" width="80" alt="">@endif</td><td>{{ $c->title }}<small>{{ \Illuminate\Support\Str::limit($c->body,80) }}</small></td><td>{{ $c->link_url }}</td><td>{{ $c->sort_order }}</td><td><span class="badge {{ $c->is_active?'badge-success':'badge-warning' }}">{{ $c->is_active?'Active':'Hidden' }}</span></td><td><div class="actions"><button class="btn btn-light" type="button" data-open-modal="editCard{{ $c->id }}"><i class="fas fa-pen"></i> Edit</button><form method="POST" action="{{ route('admin.page-content.cards.destroy',$c) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form></div></td></tr>
@empty<tr><td colspan="6" class="empty">No cards on this page yet.</td></tr>@endforelse
</tbody></table></div></div></div>
@endif
</div>

@if($hasMedia)
<div class="modal" id="addSlideModal"><div class="modal-card"><div class="modal-head"><h2>Add Slide</h2><button class="icon-btn" type="button" data-close-modal>&times;</button></div><form method="POST" action="{{ route('admin.page-content.slides.store') }}" enctype="multipart/form-data"><div class="form-grid">@csrf<input type="hidden" name="page" value="{{ $page }}"><label>Title<input name="title" placeholder="e.g. Youth Sunday Celebration" maxlength="180"></label><label>Subtitle<input name="subtitle" placeholder="e.g. Join us this Sunday at 9am" maxlength="255"></label><label>Media type<select name="media_type"><option value="image">image</option><option value="video">video</option></select></label><label>Link URL<input name="link_url" placeholder="https://example.com/event"></label><label>Sort order<input type="number" name="sort_order" placeholder="0" value="0"></label><label>Upload<input type="file" name="media_file" accept="image/*,video/*"></label><label class="span-2 checkbox"><input type="checkbox" name="is_active" value="1" checked> Active</label></div><div class="modal-actions"><button class="btn btn-light" data-close-modal type="button">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div></form></div></div>
<div class="modal" id="addCardModal"><div class="modal-card"><div class="modal-head"><h2>Add Card</h2><button class="icon-btn" type="button" data-close-modal>&times;</button></div><form method="POST" action="{{ route('admin.page-content.cards.store') }}" enctype="multipart/form-data"><div class="form-grid">@csrf<input type="hidden" name="page" value="{{ $page }}"><label>Title<input name="title" placeholder="e.g. Worship Night"></label><label>Link URL<input name="link_url" placeholder="https://example.com/worship"></label><label class="span-2">Body<textarea name="body" placeholder="Short card description..."></textarea></label><label>Icon (FA class)<input name="icon" id="cardIconInput" placeholder="fa-star" oninput="document.getElementById('cardIconPreview').className='fas '+this.value"></label><label>Image upload<input type="file" name="image_file" accept="image/*"></label><label>Sort order<input type="number" name="sort_order" placeholder="0" value="0"></label><label class="span-2">Preview: <i id="cardIconPreview" class="fas fa-star"></i> <img id="cardImgPreview" width="80" alt="" style="display:none"></label><label class="span-2 checkbox"><input type="checkbox" name="is_active" value="1" checked> Active</label></div><div class="modal-actions"><button class="btn btn-light" data-close-modal type="button">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div></form></div></div>

@foreach($slides as $s)
<div class="modal" id="editSlide{{ $s->id }}"><div class="modal-card"><div class="modal-head"><h2>Edit Slide</h2><button class="icon-btn" type="button" data-close-modal>&times;</button></div><form method="POST" action="{{ route('admin.page-content.slides.update',$s) }}" enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="page" value="{{ $s->page }}"><div class="form-grid"><label>Title<input name="title" value="{{ $s->title }}" maxlength="180"></label><label>Subtitle<input name="subtitle" value="{{ $s->subtitle }}" maxlength="255"></label><label>Media type<select name="media_type"><option value="image" @selected($s->media_type==='image')>image</option><option value="video" @selected($s->media_type==='video')>video</option></select></label><label>Link URL<input name="link_url" value="{{ $s->link_url }}"></label><label>Sort order<input type="number" name="sort_order" min="0" value="{{ $s->sort_order }}"></label><label>Replace media<input type="file" name="media_file" accept="image/*,video/*"></label><label class="span-2 checkbox"><input type="checkbox" name="is_active" value="1" @checked($s->is_active)> Active</label></div><div class="modal-actions"><button class="btn btn-light" data-close-modal type="button">Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div></form></div></div>
@endforeach
@foreach($cards as $c)
<div class="modal" id="editCard{{ $c->id }}"><div class="modal-card"><div class="modal-head"><h2>Edit Card</h2><button class="icon-btn" type="button" data-close-modal>&times;</button></div><form method="POST" action="{{ route('admin.page-content.cards.update',$c) }}" enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="page" value="{{ $c->page }}"><div class="form-grid"><label>Title<input name="title" value="{{ $c->title }}" maxlength="180"></label><label>Link URL<input name="link_url" value="{{ $c->link_url }}"></label><label class="span-2">Body<textarea name="body" maxlength="2000">{{ $c->body }}</textarea></label><label>Icon (FA class)<input name="icon" value="{{ $c->icon }}" placeholder="fa-star"></label><label>Replace image<input type="file" name="image_file" accept="image/*"></label><label>Sort order<input type="number" name="sort_order" min="0" value="{{ $c->sort_order }}"></label><label class="span-2 checkbox"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Active</label></div><div class="modal-actions"><button class="btn btn-light" data-close-modal type="button">Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div></form></div></div>
@endforeach
@endif
@endsection
@push('styles')<style>
.page-text-help{margin:0 0 14px}
.page-text-group{margin-bottom:16px}
.page-text-group h2{margin:0 0 14px;font-size:1.05rem}
.page-text-field{display:grid;gap:6px;min-width:0}
.page-text-field>label{display:flex;align-items:center;gap:8px;font-weight:700;font-size:.875rem}
.page-text-field textarea{min-height:84px}
.page-text-meta{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.page-text-meta .checkbox{display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:600}
.page-text-actions{position:sticky;bottom:0;padding:12px 0;display:flex;justify-content:flex-end;background:linear-gradient(transparent,var(--bg, #f8f7fc) 40%)}
</style>@endpush
@push('scripts')<script>document.querySelector('#addCardModal input[name=image_file]')?.addEventListener('change',e=>{const f=e.target.files[0];if(f){const img=document.getElementById('cardImgPreview');img.src=URL.createObjectURL(f);img.style.display='block';}});</script>@endpush
