@props([
    'entity',
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => 'انتخاب کنید',
    'class' => 'form-select erp-remote-select',
    'allowClear' => true,
    'minimumInputLength' => null,
    'filters' => [],
    'multiple' => false,
])

@php
    use App\Services\ErpScaleHardeningService;

    $selectId = $id ?: 'erp-remote-' . md5($name . $entity . uniqid('', true));
    $selectedIds = [];
    if ($multiple && is_array($value)) {
        $selectedIds = array_values(array_filter(array_map('intval', $value)));
    } elseif (!$multiple && $value !== null && $value !== '') {
        $selectedIds = [(int) $value];
    }
    $resolved = $selectedIds !== []
        ? app(ErpScaleHardeningService::class)->resolveByIds(auth()->user(), $entity, $selectedIds, $filters)
        : [];
    $minimumInputLength = $minimumInputLength ?? (int) config('erp_scale.remote_lookup.minimum_input_length', 2);
@endphp

<select
    {{ $attributes->merge(['class' => trim($class . ' erp-remote-select')]) }}
    name="{{ $name }}"
    id="{{ $selectId }}"
    @if($multiple) multiple @endif
    data-entity="{{ $entity }}"
    data-placeholder="{{ $placeholder }}"
    data-allow-clear="{{ $allowClear ? '1' : '0' }}"
    data-minimum-input-length="{{ $minimumInputLength }}"
    data-filters='@json($filters)'
    data-lookup-url="{{ route('erp.scale-hardening.lookup') }}"
>
    @if($allowClear)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($resolved as $row)
        <option value="{{ $row['id'] }}" selected>{{ $row['text'] }}</option>
    @endforeach
</select>
