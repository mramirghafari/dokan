<?php

/** @deprecated Full bundle; JS icons are inlined via partials/ui-icons-runtime.blade.php */
$root = dirname(__DIR__);
$icons = require $root . '/config/ui_icons.php';
$json = json_encode($icons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$js = <<<JS
(function () {
    var icons = {$json};

    function resolveSize(className) {
        if (/\\bti-lg\\b/.test(className)) return 24;
        if (/\\bti-xs\\b/.test(className)) return 16;
        if (/\\bti-sm\\b/.test(className)) return 18;
        if (/\\bti-md\\b/.test(className)) return 22;
        return 20;
    }

    window.uiIcon = function (name, className, size) {
        var paths = icons[name];
        if (!paths) return '';
        className = (className || '').replace(/\\bti-(xs|sm|md|lg)\\b/g, '').trim();
        size = size || resolveSize(className || '');
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size +
            '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ui-icon' +
            (className ? ' ' + className : '') + '" aria-hidden="true">' + paths + '</svg>';
    };
})();

JS;

file_put_contents($root . '/public/assets/js/ui-icons.js', $js);
echo "Wrote public/assets/js/ui-icons.js\n";
