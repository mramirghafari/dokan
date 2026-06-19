<?php

/** Remove node-waves and perfect-scrollbar asset tags from blade/php views. */

$root = dirname(__DIR__);
$patterns = [
    '/\s*<link[^>]*vendor\/libs\/node-waves\/node-waves\.css[^>]*>\s*\r?\n?/i',
    '/\s*<link[^>]*vendor\/libs\/perfect-scrollbar\/perfect-scrollbar\.css[^>]*>\s*\r?\n?/i',
    '/\s*<script[^>]*vendor\/libs\/node-waves\/node-waves\.js[^>]*><\/script>\s*\r?\n?/i',
    '/\s*<script[^>]*vendor\/libs\/perfect-scrollbar\/perfect-scrollbar\.js[^>]*><\/script>\s*\r?\n?/i',
];

$stats = ['files' => 0, 'removed' => 0];
$dirs = [$root . '/resources/views'];

foreach ($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
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
}

echo "Stripped {$stats['removed']} tags from {$stats['files']} files\n";
