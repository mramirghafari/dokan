<?php

$root = dirname(__DIR__) . '/resources/views';
$missing = [];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if (!preg_match('/\.(blade\.php|php)$/', $f->getFilename())) {
        continue;
    }
    $c = file_get_contents($f->getPathname());
    if (str_contains($c, 'main.js') && !str_contains($c, 'helpers.js')) {
        $missing[] = str_replace('\\', '/', substr($f->getPathname(), strlen(dirname(__DIR__)) + 1));
    }
}

echo count($missing) . " main.js without helpers:\n";
foreach ($missing as $m) {
    echo $m . "\n";
}
