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
