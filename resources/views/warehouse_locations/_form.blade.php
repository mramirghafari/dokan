<form action="{{ $action }}" method="POST" novalidate>
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <div class="mb-3">
        <label class="form-label" for="store_id">انبار<small style="color: red">*</small></label>
        <select class="select2 form-select" id="store_id" name="store_id" required>
            <option value="">انتخاب کنید</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}"
                    {{ (string) old('store_id', $location->store_id ?? '') === (string) $store->id ? 'selected' : '' }}>
                    {{ $store->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="parent_id">مکان بالادستی</label>
        <select class="select2 form-select" id="parent_id" name="parent_id" data-allow-clear="true">
            <option value="">بدون بالادست</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}"
                    {{ (string) old('parent_id', $location->parent_id ?? '') === (string) $parent->id ? 'selected' : '' }}>
                    {{ $parent->store->title ?? '' }} - {{ $parent->path ?: $parent->code }} - {{ $parent->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="type">نوع مکان<small style="color: red">*</small></label>
        <select class="form-select" id="type" name="type" required>
            @foreach ($types as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}"
                    {{ old('type', $location->type ?? 'rack') === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="code">کد مکان<small style="color: red">*</small></label>
        <input class="form-control" id="code" name="code" type="text"
            value="{{ old('code', $location->code ?? '') }}" placeholder="مثلا A-01-03" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="title">عنوان مکان<small style="color: red">*</small></label>
        <input class="form-control" id="title" name="title" type="text"
            value="{{ old('title', $location->title ?? '') }}" placeholder="قفسه مواد شوینده" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="sort_order">ترتیب نمایش</label>
        <input class="form-control" id="sort_order" name="sort_order" type="number" min="0"
            value="{{ old('sort_order', $location->sort_order ?? 0) }}">
    </div>
    <div class="form-check form-switch mb-4">
        <input class="form-check-input" id="is_active" name="is_active" type="checkbox"
            {{ old('is_active', $location->is_active ?? 1) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">فعال</label>
    </div>
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn btn-label-secondary" href="{{ route('warehouse-locations.index') }}">بازگشت</a>
</form>
