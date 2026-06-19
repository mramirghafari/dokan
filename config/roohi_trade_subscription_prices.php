<?php

/**
 * بازه‌های قیمت اشتراک پنل روحی ترید — سال ۱۴۰۵
 *
 * هر SKU فقط در بازه‌هایی که فروش داشته ردیف دارد.
 * دوره ۱۸ تا ۳۱ اردیبهشت: پیش‌پرداخت و تکمیل وجه جدا ثبت می‌شوند.
 */
return [
    'ROOHI-SUB-1M' => [
        [
            'price_type' => 'sale',
            'amount' => 2_900_000,
            'starts_at' => '1405/01/01',
            'ends_at' => '1405/01/08',
            'metadata' => ['label' => 'اشتراک یک ماهه'],
        ],
    ],

    'ROOHI-SUB-2M' => [
        [
            'price_type' => 'sale',
            'amount' => 2_900_000,
            'starts_at' => '1405/01/09',
            'ends_at' => '1405/01/29',
            'metadata' => ['label' => 'اشتراک دو ماهه'],
        ],
        [
            'price_type' => 'sale',
            'amount' => 4_900_000,
            'starts_at' => '1405/02/11',
            'ends_at' => '1405/02/17',
            'metadata' => ['label' => 'اشتراک دو ماهه'],
        ],
        [
            'price_type' => 'prepayment',
            'amount' => 2_900_000,
            'starts_at' => '1405/02/18',
            'ends_at' => '1405/02/31',
            'metadata' => ['label' => 'پیش‌پرداخت اشتراک دو ماهه', 'subscription_months' => 2],
        ],
        [
            'price_type' => 'completion',
            'amount' => 5_900_000,
            'starts_at' => '1405/02/18',
            'ends_at' => '1405/02/31',
            'metadata' => ['label' => 'تکمیل وجه اشتراک دو ماهه', 'subscription_months' => 2],
        ],
    ],

    'ROOHI-SUB-3M' => [
        [
            'price_type' => 'sale',
            'amount' => 4_900_000,
            'starts_at' => '1405/01/01',
            'ends_at' => '1405/01/08',
            'metadata' => ['label' => 'اشتراک سه ماهه'],
        ],
        [
            'price_type' => 'sale',
            'amount' => 4_900_000,
            'starts_at' => '1405/01/09',
            'ends_at' => '1405/01/29',
            'metadata' => ['label' => 'اشتراک سه ماهه'],
        ],
        [
            'price_type' => 'sale',
            'amount' => 4_900_000,
            'starts_at' => '1405/01/30',
            'ends_at' => '1405/02/10',
            'metadata' => ['label' => 'اشتراک سه ماهه'],
        ],
    ],
];
