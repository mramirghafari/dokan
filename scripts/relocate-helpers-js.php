<?php

/** Move helpers.js from <head> to before menu.js in layout footers. */

$root = dirname(__DIR__);
$removePattern = '/\s*<script[^>]*vendor\/js\/helpers\.js[^>]*><\/script>\s*\r?\n?/i';
$insertBlock = '<script src="{{ asset(\'assets/\') }}/vendor/libs/hammer/hammer.js"></script>' . "\n"
    . '<script src="{{ asset(\'assets/\') }}/vendor/js/helpers.js"></script>' . "\n";

$stats = ['stripped' => 0, 'inserted' => 0, 'files' => 0];

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

    $content = preg_replace_callback($removePattern, function () use (&$stats) {
        $stats['stripped']++;

        return '';
    }, $content);

    if (str_contains($content, 'menu.js') && !str_contains($content, 'helpers.js')) {
        $replaced = preg_replace(
            '/(\s*)(<script[^>]*vendor\/js\/menu\.js[^>]*><\/script>)/i',
            '$1' . $insertBlock . '$1$2',
            $content,
            1,
            $count
        );

        if ($count) {
            $content = $replaced;
            $stats['inserted']++;
        }
    } elseif (str_contains($content, 'main.js') && !str_contains($content, 'helpers.js') && !str_contains($content, 'menu.js')) {
        $replaced = preg_replace(
            '/(\s*)(<script[^>]*\/js\/main\.js[^>]*><\/script>)/i',
            '$1' . $insertBlock . '$1$2',
            $content,
            1,
            $count
        );

        if ($count) {
            $content = $replaced;
            $stats['inserted']++;
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $stats['files']++;
    }
}

echo "Relocated helpers.js: {$stats['stripped']} removed from head, {$stats['inserted']} inserted before menu/main in {$stats['files']} files\n";
