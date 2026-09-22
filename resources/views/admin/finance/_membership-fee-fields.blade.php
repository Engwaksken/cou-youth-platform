@php($f=$fee)
<div class="form-grid">
<label>Name<input name="name" required value="{{ old('name',$f->name ?? '') }}"></label>
<label>Year<input type="number" name="year" min="2020" max="2100" required value="{{ old('year',$f->year ?? date('Y')) }}"></label>
<label>Amount<input type="number" name="amount" min="0" step="0.01" required value="{{ old('amount',$f->amount ?? '') }}"></label>
<label>Currency<input name="currency" maxlength="3" required value="{{ old('currency',$f->currency ?? 'UGX') }}"></label>
<label>Church unit<select name="organisation_unit_id"><option value="">All units</option>@foreach($organisations as $o)<option value="{{ $o->id }}" @selected((string)old('organisation_unit_id',$f->organisation_unit_id ?? '')===(string)$o->id)>{{ $o->name }}</option>@endforeach</select></label>
<label>Due date<input type="date" name="due_date" value="{{ old('due_date',$f->due_date ?? '') }}"></label>
<label class="span-2">Description<textarea name="description" rows="4">{{ old('description',$f->description ?? '') }}</textarea></label>
<label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$f->is_active ?? true))> Active</label>
</div>
