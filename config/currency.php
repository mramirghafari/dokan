<?php

return [
    'default' => env('CURRENCY_DEFAULT', 'rial'),
    'code' => env('CURRENCY_CODE', 'IRR'),
    'labels' => [
        'rial' => 'ریال',
        'toman' => 'تومان',
    ],
    'legacy_map' => [
        0 => 'rial',
        1 => 'toman',
        2 => 'rial',
        '0' => 'rial',
        '1' => 'toman',
        '2' => 'rial',
        'toman' => 'toman',
        'rial' => 'rial',
    ],
];
