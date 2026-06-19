<meta charset="UTF-8" />
<meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    name="viewport" />
<meta content="" name="description" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
<link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
<link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
<link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
<script src="{{ asset('assets/') }}/js/config.js"></script>
@php
    $rtlCssPath = public_path('assets/css/rtl.css');
    $rtlCssVersion = is_file($rtlCssPath) ? filemtime($rtlCssPath) : time();
@endphp
<link href="{{ asset('assets/') }}/css/rtl.css?v={{ $rtlCssVersion }}" rel="stylesheet" />
