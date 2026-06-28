<?php

$path = dirname(__DIR__) . '/app/Http/Controllers/AccountingController.php';
$content = file_get_contents($path);

$pattern = '/public function (store|approve|reject|cancel|pay|close|post|merge|reconcile|charge|spend|settle|make|reverse|copy|update|createOpening)[A-Za-z0-9_]*\([^)]*\)[^{]*\{/';

preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
$insertions = 0;

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
    if (str_contains($body, 'logAccounting(') || !preg_match('/Alert::(success|warning|info)\s*\(/', $body)) {
        continue;
    }

    preg_match('/function\s+(\w+)/', $match[0], $nameMatch);
    $method = $nameMatch[1] ?? 'action';
    $action = preg_match('/^(store|create|copy|merge|import)/', $method) ? 'create' : 'update';
    $label = trim(preg_replace('/([A-Z])/', ' $1', preg_replace('/^(store|update|approve|reject|cancel|pay|close|post|merge|reconcile|charge|spend|settle|make|reverse|copy|createOpening)/', '', $method))) ?: $method;

    $logLine = "\n        \$this->logAccounting('{$action}', 'عملیات حسابداری: {$label}', null, 'accounting.{$method}');";

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

file_put_contents($path, $content);
echo "Injected logging into {$insertions} accounting methods.\n";
