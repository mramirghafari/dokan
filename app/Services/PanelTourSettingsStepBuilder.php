<?php



namespace App\Services;



class PanelTourSettingsStepBuilder

{

    public function buildFieldSteps(string $routeName): array

    {

        $groups = match ($routeName) {

            'settings.salesScenario' => ['sales_scenario'],

            'settings.index' => (array) config('panel_settings.settings_index_group_order', []),

            default => [],

        };



        if ($groups === []) {

            return [];

        }



        $definitions = (array) config('panel_settings.definitions', []);

        $groupLabels = (array) config('panel_settings.groups', []);

        $tourTexts = (array) config('panel_tour_setting_fields', []);

        $steps = [];



        if ($routeName === 'settings.index') {

            $steps[] = [

                'title' => 'ماژول‌های سیستم — شروع از اینجا',

                'text' => 'اولین بخش مهم همین کارت‌های قابلیت است: مشخص کنید فروش، انبار، CRM، مالی و… برای این پنل فعال باشند یا نه. در مراحل بعد هر سوییچ را جداگانه توضیح می‌دهیم. توصیه: از بالای صفحه شروع کنید و پایین بروید.',

                'target' => '#tour-settings-features',

                'scroll' => 'start',

            ];



            $steps = array_merge($steps, $this->buildFeatureModuleSteps($definitions, $tourTexts));



            $steps[] = [

                'title' => 'سناریوی فروش — قدم بعدی مهم',

                'text' => 'بعد از فعال‌کردن ماژول‌ها، حتماً «سناریوی فروش» را در منوی اطلاعات پایه باز کنید و مسیر فاکتور تا انبار و حسابداری را مشخص کنید. آن صفحه تور جداگانه دارد.',

                'target' => '.menu-item.sales-scenario > .menu-link',

                'openMenu' => 'basicdata',

                'scroll' => 'nearest',

            ];

        }



        foreach ($groups as $groupKey) {

            $groupFields = [];



            foreach ($definitions as $key => $definition) {

                if (($definition['group'] ?? '') !== $groupKey) {

                    continue;

                }



                if (!empty($definition['hidden'])) {

                    continue;

                }



                if (str_starts_with((string) $key, 'feature_')) {

                    continue;

                }



                $groupFields[$key] = $definition;

            }



            if ($groupFields === []) {

                continue;

            }



            $steps[] = [

                'title' => 'بخش «' . ($groupLabels[$groupKey] ?? $groupKey) . '»',

                'text' => 'این کارت شامل تنظیمات «' . ($groupLabels[$groupKey] ?? $groupKey) . '» است. در چند مرحله بعد هر فیلد را جداگانه توضیح می‌دهیم.',

                'target' => '#tour-settings-group-' . $groupKey,

                'optional' => true,

                'scroll' => 'center',

            ];



            foreach ($groupFields as $key => $definition) {

                $override = $tourTexts[$key] ?? [];



                $steps[] = [

                    'title' => $override['title'] ?? ($definition['label'] ?? $key),

                    'text' => $override['text'] ?? $this->buildDefaultFieldText($key, $definition),

                    'target' => '#tour-setting-' . $key,

                    'optional' => true,

                    'scroll' => 'center',

                ];

            }

        }



        if ($routeName === 'settings.index') {

            $steps[] = [

                'title' => 'چینش منوی پنل',

                'text' => 'در انتها می‌توانید ترتیب منوی کناری را با عدد تنظیم کنید: عدد کوچکتر = بالاتر در منو. این بخش اولویت پایین‌تری دارد؛ بعد از ماژول‌ها و سناریوی فروش سراغش بیایید.',

                'target' => '#tour-settings-navigation',

                'optional' => true,

                'scroll' => 'center',

            ];

        }



        return $steps;

    }



    private function buildFeatureModuleSteps(array $definitions, array $tourTexts): array

    {

        $steps = [];



        foreach ($definitions as $key => $definition) {

            if (!str_starts_with((string) $key, 'feature_')) {

                continue;

            }



            if (!empty($definition['hidden'])) {

                continue;

            }



            $override = $tourTexts[$key] ?? [];



            $steps[] = [

                'title' => $override['title'] ?? ($definition['label'] ?? $key),

                'text' => $override['text'] ?? $this->buildDefaultFieldText($key, $definition),

                'target' => '#tour-feature-' . $key,

                'optional' => true,

                'scroll' => 'center',

            ];

        }



        return $steps;

    }



    private function buildDefaultFieldText(string $key, array $definition): string

    {

        $label = (string) ($definition['label'] ?? $key);

        $description = trim((string) ($definition['description'] ?? ''));

        $type = (string) ($definition['type'] ?? 'text');

        $intro = $description !== '' ? $description . ' ' : '';



        if ($type === 'boolean') {

            return $intro . 'این سوییچ را روشن کنید تا «' . $label . '» برای این پنل فعال شود، یا خاموش کنید تا غیرفعال بماند. خط «مقدار پایه» پیش‌فرض کل سیستم را نشان می‌دهد؛ بج آبی «اختصاصی پنل» یعنی برای همین پنل جداگانه ذخیره شده است.';

        }



        if (in_array($type, ['select', 'multiselect'], true) && !empty($definition['options'])) {

            $options = collect($definition['options'])

                ->take(6)

                ->map(fn ($optionLabel) => '«' . $optionLabel . '»')

                ->implode('، ');



            $suffix = $type === 'multiselect'

                ? 'می‌توانید چند گزینه را همزمان انتخاب کنید.'

                : 'فقط یک گزینه را انتخاب کنید.';



            return $intro . 'گزینه‌های رایج: ' . $options . '. ' . $suffix . ' انتخاب شما تعیین می‌کند سیستم در این بخش چگونه رفتار کند.';

        }



        if ($type === 'number') {

            return $intro . 'یک عدد وارد کنید. صفر معمولاً یعنی «بدون محدودیت» یا «غیرفعال» — بسته به موضوع فیلد. بعد از تغییر حتماً پایین صفحه ذخیره کنید.';

        }



        if ($type === 'json') {

            return $intro . 'این مقدار به‌صورت داخلی ذخیره می‌شود و معمولاً از خود صفحه (مثل مدیریت ستون‌های جدول) تنظیم می‌گردد.';

        }



        return $intro . 'مقدار این فیلد را برای پنل جاری تنظیم کنید. «مقدار پایه» پیش‌فرض سیستم است؛ در صورت نیاز مقدار اختصاصی پنل را انتخاب یا وارد کنید.';

    }

}


