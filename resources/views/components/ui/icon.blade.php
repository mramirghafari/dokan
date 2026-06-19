@props([
    'name',
    'class' => '',
    'size' => null,
])

@php
    $name = \App\Support\UiIcon::normalizeName((string) $name);
    $extraClass = trim((string) $class);
    $resolvedSize = $size;

    if ($resolvedSize === null) {
        if (preg_match('/\bti-lg\b/', $extraClass)) {
            $resolvedSize = 24;
        } elseif (preg_match('/\bti-xs\b/', $extraClass)) {
            $resolvedSize = 16;
        } elseif (preg_match('/\bti-sm\b/', $extraClass)) {
            $resolvedSize = 18;
        } else {
            $resolvedSize = 20;
        }
    }

    $extraClass = trim(preg_replace('/\bti-(xs|sm|md|lg)\b/', '', $extraClass) ?? '');
    $paths = config('ui_icons.' . $name);
@endphp

@if ($paths)
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $resolvedSize }}" height="{{ $resolvedSize }}"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" class="ui-icon {{ $extraClass }}" aria-hidden="true" {{ $attributes }}>
        {!! $paths !!}
    </svg>
@endif
