<?php

/**
 * Migrate Log::create() in controllers to ActivityLogService.
 * Run: php scripts/migrate-activity-log-calls.php
 */

$root = dirname(__DIR__);
$controllersDir = $root . '/app/Http/Controllers';

$sectionMap = [
    'customer' => 'crm', 'Customers' => 'crm', 'Customer' => 'crm',
    'invoice' => 'invoice', 'Pishfactor' => 'invoice', 'Factor' => 'invoice', 'pishfactor' => 'invoice',
    'product' => 'product', 'Product' => 'product',
    'stock' => 'warehouse', 'Stock' => 'warehouse', 'warehouse' => 'warehouse', 'Warehouse' => 'warehouse',
    'account' => 'accounting', 'Account' => 'accounting', 'Accounts' => 'accounting',
    'receipt' => 'accounting', 'Receipt' => 'accounting',
    'role' => 'roles', 'Role' => 'roles', 'permission' => 'roles', 'Permission' => 'roles',
    'tenant' => 'organization', 'Tenant' => 'organization', 'Tenants' => 'organization',
    'organization' => 'organization', 'Organization' => 'organization',
    'region' => 'sales', 'Region' => 'sales', 'area' => 'sales', 'Area' => 'sales',
    'target' => 'sales', 'Target' => 'sales', 'task' => 'sales', 'Tasks' => 'sales',
    'transfer' => 'warehouse', 'Transfer' => 'warehouse',
    'delivery' => 'delivery', 'Delivery' => 'delivery',
    'supplier' => 'procurement', 'Supplier' => 'procurement',
    'category' => 'product', 'Category' => 'product', 'brand' => 'product', 'Brand' => 'product',
    'unit' => 'product', 'Unit' => 'product',
    'store' => 'warehouse', 'Store' => 'warehouse', 'depot' => 'warehouse', 'Depot' => 'warehouse',
    'material' => 'warehouse', 'Material' => 'warehouse',
    'city' => 'organization', 'City' => 'organization',
    'terminal' => 'system', 'Terminal' => 'system',
    'repair' => 'warehouse', 'Repair' => 'warehouse',
    'abortion' => 'warehouse', 'Abortion' => 'warehouse',
    'factor' => 'invoice', 'FactorSetting' => 'settings',
    'location' => 'warehouse', 'WarehouseLocation' => 'warehouse',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir));
$updatedFiles = 0;
$migrated = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);
    if ($content === false || !preg_match('/\bLog::create\s*\(/', $content)) {
        continue;
    }

    // Skip non-activity Log models
    if (preg_match('/\b(CrmCallLog|BiRefreshLog|CrmIntegrationSyncLog|CrmPublicApiRequestLog|EcommerceSyncLog)::create\s*\(/', $content)
        && !preg_match("/'ip'\s*=>/", $content)) {
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

    $content = preg_replace('/^use App\\\\Models\\\\Log;\r?\n/m', '', $content);

    $content = preg_replace_callback(
        '/Log::create\s*\(\s*\[(.*?)\]\s*\)\s*;/s',
        function (array $matches) use ($sectionMap, &$migrated) {
            $block = $matches[1];

            if (!preg_match("/['\"]action['\"]\s*=>\s*['\"](\w+)['\"]/", $block, $actionMatch)) {
                return $matches[0];
            }
            $action = $actionMatch[1];

            if (!preg_match("/['\"]description['\"]\s*=>\s*(.+)/s", $block, $descMatch)) {
                return $matches[0];
            }
            $description = trim(preg_replace('/\s*$/', '', rtrim(trim($descMatch[1]), ",\n\r\t ")));

            $section = null;
            $sectionId = null;
            if (preg_match("/['\"]section['\"]\s*=>\s*['\"](\w+)['\"]/", $block, $sectionMatch)) {
                $section = $sectionMatch[1];
            }
            if (preg_match("/['\"]section_id['\"]\s*=>\s*\$(\w+)/", $block, $sidMatch)) {
                $sectionId = $sidMatch[1];
            }

            $sourceVar = null;
            if (preg_match('/\$(\w+)->(?:name|description|title|id|number|code)/', $description, $varMatch)) {
                $sourceVar = $varMatch[1];
            } elseif ($sectionId) {
                $sourceVar = $sectionId;
            }

            $resolvedSection = 'system';
            if ($section && isset($sectionMap[$section])) {
                $resolvedSection = $sectionMap[$section];
            } elseif ($sourceVar && isset($sectionMap[$sourceVar])) {
                $resolvedSection = $sectionMap[$sourceVar];
            } elseif ($section) {
                $resolvedSection = $section;
            }

            $eventKey = ($sourceVar ? strtolower($sourceVar) : $resolvedSection) . '.' . $action;
            $migrated++;

            if ($sourceVar) {
                return "ActivityLogService::safeLogModel('{$action}', {$description}, \${$sourceVar}, ['section' => '{$resolvedSection}', 'event_key' => '{$eventKey}']);";
            }

            return "ActivityLogService::safeLog('{$action}', {$description}, null, ['section' => '{$resolvedSection}', 'event_key' => '{$eventKey}']);";
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        $updatedFiles++;
        echo "Updated: {$path}\n";
    }
}

echo "\nFiles updated: {$updatedFiles}, Log::create migrated: {$migrated}\n";
