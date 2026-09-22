@php($p=$plan)
<div class="form-grid">
<label>Year<input type="number" name="year" min="2020" max="2100" required value="{{ old('year',$p->year ?? date('Y')) }}"></label>
<label>Title<input name="title" required maxlength="180" value="{{ old('title',$p->title ?? '') }}"></label>
<label class="span-2">Objective<textarea name="objective" rows="3">{{ old('objective',$p->objective ?? '') }}</textarea></label>
<label class="span-2">Activity<textarea name="activity" rows="3">{{ old('activity',$p->activity ?? '') }}</textarea></label>
<label>Responsible person<input name="responsible_person" value="{{ old('responsible_person',$p->responsible_person ?? '') }}"></label>
<label>Department<input name="department" value="{{ old('department',$p->department ?? '') }}"></label>
<label>Start date<input type="date" name="start_date" value="{{ old('start_date',$p->start_date ?? '') }}"></label>
<label>End date<input type="date" name="end_date" value="{{ old('end_date',$p->end_date ?? '') }}"></label>
<label>Budget<input type="number" min="0" step="0.01" name="budget" value="{{ old('budget',$p->budget ?? 0) }}"></label>
<label>Progress %<input type="number" min="0" max="100" name="progress_percent" value="{{ old('progress_percent',$p->progress_percent ?? 0) }}"></label>
<label>Status<select name="status">@foreach(['planned','in_progress','completed','on_hold','cancelled'] as $s)<option value="{{ $s }}" @selected(old('status',$p->status ?? 'planned')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select></label>
<label class="span-2">Notes<textarea name="notes" rows="3">{{ old('notes',$p->notes ?? '') }}</textarea></label>
</div>
