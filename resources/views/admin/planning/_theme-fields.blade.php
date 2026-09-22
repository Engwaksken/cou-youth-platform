@php($t=$theme)
<div class="form-grid">
<label>Year<input type="number" name="year" min="2020" max="2100" required value="{{ old('year',$t->year ?? date('Y')) }}"></label>
<label>Theme<input name="theme" required maxlength="255" value="{{ old('theme',$t->theme ?? '') }}"></label>
<label class="span-2">Scripture reference<input name="scripture_reference" value="{{ old('scripture_reference',$t->scripture_reference ?? '') }}"></label>
<label class="span-2">Description<textarea name="description" rows="5">{{ old('description',$t->description ?? '') }}</textarea></label>
<label>Banner / image<input type="file" name="image" accept="image/png,image/jpeg,image/webp"></label>
<label class="checkbox"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$t->is_published ?? false))> Published</label>
</div>
