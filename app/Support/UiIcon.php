<?php

namespace App\Support;

class UiIcon
{
    public static function normalizeName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/^ti\s+/', '', $name);
        $name = preg_replace('/^ti-/', '', $name);

        return $name;
    }

    public static function html(string $name, string $class = '', ?int $size = null): string
    {
        $name = self::normalizeName($name);
        $paths = config('ui_icons.' . $name);
        if (!$paths) {
            return '';
        }

        $extraClass = trim(preg_replace('/\bti-(xs|sm|md|lg)\b/', '', $class) ?? '');
        $resolvedSize = $size ?? self::resolveSize($class);

        $classAttr = $extraClass !== '' ? ' class="ui-icon ' . e($extraClass) . '"' : ' class="ui-icon"';

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $resolvedSize . '" height="' . $resolvedSize . '"'
            . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"'
            . ' stroke-linejoin="round"' . $classAttr . ' aria-hidden="true">' . $paths . '</svg>';
    }

    private static function resolveSize(string $class): int
    {
        if (preg_match('/\bti-lg\b/', $class)) {
            return 24;
        }
        if (preg_match('/\bti-xs\b/', $class)) {
            return 16;
        }
        if (preg_match('/\bti-sm\b/', $class)) {
            return 18;
        }

        return 20;
    }
}
