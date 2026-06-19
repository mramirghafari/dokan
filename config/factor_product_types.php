<?php

/*
|--------------------------------------------------------------------------
| نوع محصولات فاکتور (Factor Product Types)
|--------------------------------------------------------------------------
|
| هر «نوع محصول» علاوه بر یک برچسب، به یک پروفایل ستون‌بندی (business_profile)
| در config/invoice_layouts.php نگاشت می‌شود. با انتخاب نوع محصول در فاکتورساز،
| ستون‌ها و حالت محاسبه مقدار (quantity_mode) به‌صورت خودکار تغییر می‌کند.
|
| legacy_map مقادیر قدیمی boolean ستون pr_type را به کلید جدید رشته‌ای نگاشت می‌کند:
|   1  => refrigerated (یخچالی)
|   0  => non_refrigerated (غیریخچالی)
|
*/

return [
    'default' => 'non_refrigerated',

    'types' => [
        'refrigerated' => [
            'label' => 'محصولات یخچالی',
            'description' => 'کالاهای زنجیره سرد: لبنیات، گوشت، مرغ، بستنی — فروش تعدادی و وزنی',
            'profile' => 'distribution',
        ],
        'non_refrigerated' => [
            'label' => 'محصولات غیریخچالی',
            'description' => 'کالاهای خشک و معمولی: خواربار، نوشیدنی، بهداشتی، لوازم خانگی',
            'profile' => 'distribution',
        ],
        'bulk_weight' => [
            'label' => 'کالای وزنی / فله',
            'description' => 'فروش بر اساس وزن خالص: میوه، حبوبات، آهن، مصالح و مواد فله',
            'profile' => 'bulk_weight',
        ],
        'pharma' => [
            'label' => 'دارویی / بهداشتی',
            'description' => 'اقلام دارویی با کد و واحد جزئی: قرص، ویال، بسته، شربت',
            'profile' => 'distribution',
        ],
        'virtual' => [
            'label' => 'محصولات مجازی / دیجیتال',
            'description' => 'کالای بدون وزن و انبار: لایسنس، کد شارژ، فایل، گیفت‌کارت',
            'profile' => 'virtual',
        ],
        'service' => [
            'label' => 'خدمات',
            'description' => 'خدمات قابل ارائه: تعمیر، نصب، مشاوره، حمل و نقل',
            'profile' => 'service',
        ],
        'education' => [
            'label' => 'خدمات آموزشی',
            'description' => 'دوره و کلاس — فروش بر اساس سرفصل و تعداد نفر',
            'profile' => 'education',
        ],
        'subscription' => [
            'label' => 'اشتراک / SaaS',
            'description' => 'پلن، مدت اشتراک (ماه) و تعداد کاربر',
            'profile' => 'subscription',
        ],
        'mixed' => [
            'label' => 'ترکیبی / عمومی',
            'description' => 'کسب‌وکار با کالاهای متنوع — کامل‌ترین حالت ستون‌ها (تعداد، وزن، واحد فرعی)',
            'profile' => 'distribution',
        ],
    ],

    'legacy_map' => [
        '1' => 'refrigerated',
        '0' => 'non_refrigerated',
        'true' => 'refrigerated',
        'false' => 'non_refrigerated',
    ],
];
