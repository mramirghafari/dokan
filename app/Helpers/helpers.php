<?php

use Hekmatinasser\Verta\Verta;

if (!function_exists('verta_date')) {
    /**
     * Convert a date to Persian/Shamsi format (date only).
     */
    function verta_date($date, string $format = 'Y/n/j'): string
    {
        if (!$date) {
            return '—';
        }
        try {
            return Verta::instance($date)->format($format);
        } catch (\Throwable $_) {
            return '—';
        }
    }
}

if (!function_exists('verta_datetime')) {
    /**
     * Convert a datetime to Persian/Shamsi format (date + time).
     */
    function verta_datetime($date, string $format = 'Y/n/j H:i'): string
    {
        if (!$date) {
            return '—';
        }
        try {
            return Verta::instance($date)->format($format);
        } catch (\Throwable $_) {
            return '—';
        }
    }
}

if (!function_exists('currency_unit')) {
    function currency_unit(?int $tenantId = null): string
    {
        $default = (string) config('currency.default', 'rial');
        $unit = \App\Services\TenantSettings::get('currency_type', $tenantId, $default);

        if (is_string($unit) || is_int($unit)) {
            $mapped = config('currency.legacy_map.' . $unit);

            if (is_string($mapped) && $mapped !== '') {
                return $mapped;
            }
        }

        return in_array($unit, ['rial', 'toman'], true) ? $unit : $default;
    }
}

if (!function_exists('currency_label')) {
    function currency_label(?int $tenantId = null): string
    {
        return (string) config('currency.labels.' . currency_unit($tenantId), 'ریال');
    }
}

if (!function_exists('org_currency_label')) {
    function org_currency_label($organization = null, ?int $tenantId = null): string
    {
        if ($organization !== null && isset($organization->currency_type)) {
            $unit = config('currency.legacy_map.' . $organization->currency_type);

            if (is_string($unit) && $unit !== '') {
                return (string) config('currency.labels.' . $unit, 'ریال');
            }
        }

        return currency_label($tenantId);
    }
}

if (!function_exists('format_currency_amount')) {
    function format_currency_amount($amount, ?int $tenantId = null, int $decimals = 0): string
    {
        return number_format((float) $amount, $decimals) . ' ' . currency_label($tenantId);
    }
}
