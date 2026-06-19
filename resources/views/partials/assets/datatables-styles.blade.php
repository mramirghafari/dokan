@php
    $bundle = $bundle ?? 'basic';
    $assets = asset('assets');
@endphp
<link href="{{ $assets }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
@if ($bundle === 'full')
    <link href="{{ $assets }}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" rel="stylesheet" />
    <link href="{{ $assets }}/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css" rel="stylesheet" />
@endif
