<?php

$controllersDir = dirname(__DIR__) . '/app/Http/Controllers';
$crmFiles = glob($controllersDir . '/Crm*.php');
$crmFiles[] = $controllersDir . '/Api/CrmPublicApiController.php';

$insertions = 0;

foreach ($crmFiles as $path) {
    if (!is_file($path)) {
        continue;
    }

    $content = file_get_contents($path);
    if (!preg_match('/public function (store|update|create)[A-Za-z0-9_]*/', $content)) {
        continue;
    }

    $original = $content;

    if (!str_contains($content, 'use App\Services\ActivityLogService;')) {
        $content = preg_replace(
            '/(namespace App\\\\Http\\\\Controllers[^;]*;)/',
            "$1\n\nuse App\\Services\\ActivityLogService;",
            $content,
            1
        );
    }

    $pattern = '/public function (store|update|create)[A-Za-z0-9_]*\([^)]*\)[^{]*\{/';
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    foreach (array_reverse($matches[0]) as $match) {
        $start = $match[1];
        $bracePos = strpos($content, '{', $start);
        if ($bracePos === false) {
            continue;
        }

        $depth = 0;
        $end = $bracePos;
        for ($i = $bracePos, $len = strlen($content); $i < $len; $i++) {
            if ($content[$i] === '{') {
                $depth++;
            } elseif ($content[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        $body = substr($content, $bracePos, $end - $bracePos + 1);
        if (str_contains($body, 'ActivityLogService::') || !preg_match('/Alert::(success|warning|info)\s*\(/', $body)) {
            continue;
        }

        preg_match('/function\s+(\w+)/', $match[0], $nameMatch);
        $method = $nameMatch[1] ?? 'action';
        $action = str_starts_with($method, 'store') || str_starts_with($method, 'create') ? 'create' : 'update';
        $label = trim(preg_replace('/([A-Z])/', ' $1', preg_replace('/^(store|update|create)/', '', $method))) ?: $method;

        $logLine = "\n        ActivityLogService::safeLog('{$action}', 'CRM: {$label}', null, ['section' => 'crm', 'event_key' => 'crm.{$method}']);";

        $newBody = preg_replace(
            '/(\n\s*)(Alert::(?:success|warning|info)\s*\()/',
            '$1' . trim($logLine) . '$1$2',
            $body,
            1
        );

        if ($newBody === $body) {
            continue;
        }

        $content = substr($content, 0, $bracePos) . $newBody . substr($content, $end + 1);
        $insertions++;
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: {$path}\n";
    }
}

echo "\nCRM logging insertions: {$insertions}\n";
