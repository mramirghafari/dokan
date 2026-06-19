<div id="dokan-toast-stack" class="dokan-toast-stack" aria-live="polite" aria-atomic="true"></div>

@include('partials.ui-icons-runtime')

@php
    $toastMessages = collect();

    if (session('toast')) {
        $toastMessages->push(session('toast'));
    }

    foreach (['success', 'error', 'warning', 'info'] as $flashType) {
        if (session($flashType)) {
            $toastMessages->push([
                'type' => $flashType === 'error' ? 'danger' : $flashType,
                'message' => session($flashType),
            ]);
        }
    }
@endphp

<script src="{{ asset('assets/js/panel-toast.js') }}"></script>

@if ($toastMessages->isNotEmpty())
    <script>
        window.__dokanToastQueue = @json($toastMessages->values());
        if (window.DokanFlushToasts) {
            window.DokanFlushToasts();
        }
    </script>
@endif
