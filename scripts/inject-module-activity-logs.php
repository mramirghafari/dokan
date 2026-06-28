<?php

$targets = [
    dirname(__DIR__) . '/app/Http/Controllers/StockController.php',
    dirname(__DIR__) . '/app/Http/Controllers/PurchaseOrderController.php',
    dirname(__DIR__) . '/app/Http/Controllers/DistributionController.php',
    dirname(__DIR__) . '/app/Http/Controllers/ReportController.php',
    dirname(__DIR__) . '/app/Http/Controllers/ContractingController.php',
    dirname(__DIR__) . '/app/Http/Controllers/PurchaseRequisitionController.php',
    dirname(__DIR__) . '/app/Http/Controllers/RoleController.php',
];

$sectionMap = [
    'StockController.php' => 'warehouse',
    'PurchaseOrderController.php' => 'procurement',
    'PurchaseRequisitionController.php' => 'procurement',
    'DistributionController.php' => 'sales',
    'ReportController.php' => 'report',
    'ContractingController.php' => 'contracting',
    'RoleController.php' => 'roles',
];

$insertions = 0;

foreach ($targets as $path) {
    if (!is_file($path)) {
        continue;
    }

    $basename = basename($path);
    $section = $sectionMap[$basename] ?? 'system';
    $content = file_get_contents($path);
    $original = $content;

    if (!str_contains($content, 'use App\Services\ActivityLogService;')) {
        $content = preg_replace(
            '/(namespace App\\\\Http\\\\Controllers[^;]*;)/',
            "$1\n\nuse App\\Services\\ActivityLogService;",
            $content,
            1
        );
    }

    $pattern = '/public function (store|update|destroy|approve|cancel|import|export|managementTemplateStore|managementScheduleStore)[A-Za-z0-9_]*\([^)]*\)[^{]*\{/';
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
        $action = preg_match('/^(store|create|import|managementTemplateStore|managementScheduleStore)/', $method) ? 'create'
            : (str_contains($method, 'destroy') || str_contains($method, 'cancel') ? 'delete' : 'update');
        $label = trim(preg_replace('/([A-Z])/', ' $1', preg_replace('/^(store|update|destroy|approve|cancel|import|export|managementTemplateStore|managementScheduleStore)/', '', $method))) ?: $method;

        $logLine = "\n        ActivityLogService::safeLog('{$action}', '{$label}', null, ['section' => '{$section}', 'event_key' => '{$section}.{$method}']);";

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

echo "\nModule logging insertions: {$insertions}\n";
