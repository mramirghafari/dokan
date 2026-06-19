<?php

/** Remove standalone ui-icons.css/js tags from blade views (runtime is inlined via partial). */

$root = dirname(__DIR__);
$patterns = [
    '/\s*<link[^>]*assets\/css\/ui-icons\.css[^>]*>\s*\r?\n?/i',
    '/\s*<script[^>]*assets\/js\/ui-icons\.js[^>]*><\/script>\s*\r?\n?/i',
];

$stats = ['files' => 0, 'removed' => 0];
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

    foreach ($patterns as $pattern) {
        $content = preg_replace_callback($pattern, function () use (&$stats) {
            $stats['removed']++;

            return '';
        }, $content);
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $stats['files']++;
    }
}

echo "Stripped {$stats['removed']} ui-icons asset tags from {$stats['files']} files\n";
