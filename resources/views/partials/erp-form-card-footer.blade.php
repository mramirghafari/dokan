@props([
    'submitLabel' => 'ذخیره',
    'submitIcon' => 'check',
    'cancelUrl' => '#',
    'hintText' => null,
    'formId' => null,
    'id' => null,
])

<div @if ($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'card-footer erp-form-card__footer d-flex flex-wrap gap-2 justify-content-between align-items-center']) }}>
    @if ($hintText)
        <span class="text-muted small mb-0">{{ $hintText }}</span>
    @endif
    <div class="d-flex flex-wrap gap-2 erp-form-card__actions">
        <a href="{{ $cancelUrl }}" class="btn btn-label-secondary waves-effect">انصراف</a>
        <button class="btn btn-primary waves-effect waves-light" type="submit"
            @if ($formId) form="{{ $formId }}" @endif>
            <x-ui.icon :name="$submitIcon" class="me-1" />
            {{ $submitLabel }}
        </button>
    </div>
</div>
