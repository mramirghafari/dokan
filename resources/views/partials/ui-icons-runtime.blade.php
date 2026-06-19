@once('ui-icons-runtime')
    <script>
        (function () {
            var icons = @json(config('ui_icons'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            function resolveSize(className) {
                if (/\bti-lg\b/.test(className)) return 24;
                if (/\bti-xs\b/.test(className)) return 16;
                if (/\bti-sm\b/.test(className)) return 18;
                if (/\bti-md\b/.test(className)) return 22;
                return 20;
            }

            function normalizeName(name) {
                return String(name || '')
                    .trim()
                    .replace(/^ti\s+/, '')
                    .replace(/^ti-/, '');
            }

            window.uiIcon = function (name, className, size) {
                name = normalizeName(name);
                var paths = icons[name];
                if (!paths) return '';
                className = (className || '').replace(/\bti-(xs|sm|md|lg)\b/g, '').trim();
                size = size || resolveSize(className || '');
                return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size +
                    '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ui-icon' +
                    (className ? ' ' + className : '') + '" aria-hidden="true">' + paths + '</svg>';
            };
        })();
    </script>
@endonce
