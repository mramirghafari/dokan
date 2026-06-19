<?php

/**
 * Replace icon font markup with <x-ui.icon> and remove font CSS links from views.
 * Run: php scripts/migrate-icons.php
 */

$root = dirname(__DIR__);
$stats = ['files' => 0, 'icons' => 0, 'links' => 0];

$linkPatterns = [
    '/^\s*<link[^>]*fontawesome\.css[^>]*>\s*\r?\n?/mi',
    '/^\s*<link[^>]*tabler-icons\.css[^>]*>\s*\r?\n?/mi',
    '/^\s*<link[^>]*flag-icons\.css[^>]*>\s*\r?\n?/mi',
];

$iconPattern = '/<i\s+class="(?:fa[srlbd]?\s+fa-([\w-]+)|ti\s+ti-([\w-]+))([^"]*)"\s*([^>]*?)>\s*<\/i>/i';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/resources/views', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!preg_match('/\.(blade\.php|php)$/', $file->getFilename())) {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    foreach ($linkPatterns as $pattern) {
        $content = preg_replace_callback($pattern, function () use (&$stats) {
            $stats['links']++;

            return '';
        }, $content);
    }

    $content = preg_replace_callback($iconPattern, function ($m) use (&$stats) {
        $stats['icons']++;
        $name = $m[2] !== '' ? $m[2] : 'fa-' . $m[1];
        $classes = trim($m[3] ?? '');
        $attrStr = $classes !== '' ? ' class="' . htmlspecialchars($classes, ENT_QUOTES) . '"' : '';

        return '<x-ui.icon name="' . $name . '"' . $attrStr . ' />';
    }, $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        $stats['files']++;
    }
}

echo "Updated {$stats['files']} files, {$stats['icons']} icons, removed {$stats['links']} link tags\n";
