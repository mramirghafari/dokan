@auth
    @include('partials.ui-icons-runtime')
    @once('panel-onboarding-tour-script')
        @php
            $tourJsPath = public_path('assets/js/panel-onboarding-tour.js');
            $tourJs = is_readable($tourJsPath) ? file_get_contents($tourJsPath) : '';
        @endphp
        @if ($tourJs !== '')
            <script>{!! $tourJs !!}</script>
        @endif
    @endonce
@endauth
