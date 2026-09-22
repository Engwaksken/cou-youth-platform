@php($s=$service)
<div class="form-grid">
<label>Title<input name="title" required value="{{ old('title',$s->title ?? '') }}"></label>
<label>Platform<select name="platform">@foreach(['YouTube','Facebook','Zoom','Teams','Other'] as $p)<option @selected(old('platform',$s->platform ?? 'YouTube')===$p)>{{ $p }}</option>@endforeach</select></label>
<label class="span-2">Stream URL<input type="url" name="stream_url" required value="{{ old('stream_url',$s->stream_url ?? '') }}"></label>
<label>Starts at<input type="datetime-local" name="starts_at" required value="{{ old('starts_at',isset($s->starts_at)?\Illuminate\Support\Carbon::parse($s->starts_at)->format('Y-m-d\TH:i'):'') }}"></label>
<label>Ends at<input type="datetime-local" name="ends_at" value="{{ old('ends_at',isset($s->ends_at)&&$s->ends_at?\Illuminate\Support\Carbon::parse($s->ends_at)->format('Y-m-d\TH:i'):'') }}"></label>
<label>Speaker / preacher<input name="speaker" value="{{ old('speaker',$s->speaker ?? '') }}"></label>
<label>Recurrence<input name="recurrence" placeholder="e.g. Weekly Sunday" value="{{ old('recurrence',$s->recurrence ?? '') }}"></label>
<label>Status<select name="status">@foreach(['scheduled','live','completed','cancelled'] as $st)<option value="{{ $st }}" @selected(old('status',$s->status ?? 'scheduled')===$st)>{{ ucfirst($st) }}</option>@endforeach</select></label>
<label>Thumbnail<input type="file" name="thumbnail" accept="image/png,image/jpeg,image/webp"></label>
<label class="span-2">Description<textarea name="description" rows="4">{{ old('description',$s->description ?? '') }}</textarea></label>
</div>
