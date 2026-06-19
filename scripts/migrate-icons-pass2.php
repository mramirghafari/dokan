<?php

/** Second pass: alternate class order + inject ui-icons assets into standalone heads. */

$root = dirname(__DIR__);
$assetBlock = '<link href="{{ asset(\'assets/css/ui-icons.css\') }}" rel="stylesheet" />' . "\n"
    . '<script src="{{ asset(\'assets/js/ui-icons.js\') }}"></script>' . "\n";

$altIconPattern = '/<i\s+class="([^"]*)\bti\s+ti-([\w-]+)([^"]*)"\s*([^>]*?)>\s*<\/i>/i';
$stats = ['icons' => 0, 'assets' => 0, 'files' => 0];

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

    $content = preg_replace_callback($altIconPattern, function ($m) use (&$stats) {
        $stats['icons']++;
        $classes = trim($m[1] . ' ' . $m[3]);

        return '<x-ui.icon name="' . $m[2] . '" class="' . htmlspecialchars($classes, ENT_QUOTES) . '" />';
    }, $content);

    if (!str_contains($content, 'ui-icons.css') && str_contains($content, '<head')) {
        if (preg_match('/<link[^>]*favicon[^>]*>\s*\r?\n/i', $content)) {
            $content = preg_replace(
                '/(<link[^>]*favicon[^>]*>\s*\r?\n)/i',
                '$1' . $assetBlock,
                $content,
                1,
                $count
            );
            if ($count) {
                $stats['assets']++;
            }
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $stats['files']++;
    }
}

echo "Pass 2: {$stats['files']} files, {$stats['icons']} icons, {$stats['assets']} asset injections\n";
