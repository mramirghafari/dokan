<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormFieldRuleService
{
    public const CUSTOMER_SETTING_KEY = 'customer_form_field_rules';

    public const INVOICE_SETTING_KEY = 'invoice_form_field_rules';

    /**
     * @return array<string, array{label: string, default: string, feature?: string}>
     */
    public function customerFieldCatalog(): array
    {
        return [
            'name' => ['label' => 'نام کامل مشتری', 'default' => 'required'],
            'tablo' => ['label' => 'تابلو مشتری', 'default' => 'required'],
            'customer_code' => ['label' => 'کد مشتری', 'default' => 'required'],
            'phone' => ['label' => 'شماره تلفن', 'default' => 'required'],
            'mobile' => ['label' => 'شماره موبایل', 'default' => 'required'],
            'national_id' => ['label' => 'کد ملی', 'default' => 'required'],
            'mapcode' => ['label' => 'مپ کد', 'default' => 'optional'],
            'customer_group_id' => ['label' => 'گروه مشتری', 'default' => 'required'],
            'sales_channel_id' => ['label' => 'کانال فروش', 'default' => 'required'],
            'customer_status_id' => ['label' => 'وضعیت مشتری', 'default' => 'optional'],
            'region_id' => ['label' => 'منطقه مشتری', 'default' => 'optional', 'feature' => 'feature_area_management'],
            'area' => ['label' => 'مسیر', 'default' => 'optional', 'feature' => 'feature_route_management'],
            'address' => ['label' => 'آدرس فروشگاه', 'default' => 'required'],
            'store_address' => ['label' => 'آدرس انبار', 'default' => 'optional'],
            'shop_lat' => ['label' => 'لوکیشن فروشگاه', 'default' => 'optional'],
            'store_lat' => ['label' => 'لوکیشن انبار', 'default' => 'optional'],
        ];
    }

    /**
     * @return array<string, array{label: string, default: string}>
     */
    public function invoiceFieldCatalog(): array
    {
        return [
            'customer' => ['label' => 'مشتری', 'default' => 'required'],
            'items' => ['label' => 'اقلام فاکتور', 'default' => 'required'],
            'description' => ['label' => 'توضیحات', 'default' => 'optional'],
            'buy_date' => ['label' => 'تاریخ خرید', 'default' => 'optional'],
            'delivery_date' => ['label' => 'تاریخ تحویل', 'default' => 'optional'],
            'payment_method' => ['label' => 'روش پرداخت', 'default' => 'optional'],
            'store_id' => ['label' => 'انبار', 'default' => 'optional'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function resolvedRules(string $form, ?int $tenantId = null): array
    {
        $catalog = $form === 'invoice' ? $this->invoiceFieldCatalog() : $this->customerFieldCatalog();
        $settingKey = $form === 'invoice' ? self::INVOICE_SETTING_KEY : self::CUSTOMER_SETTING_KEY;
        $saved = TenantSettings::get($settingKey, $tenantId, []);

        if (!is_array($saved)) {
            $saved = [];
        }

        $rules = [];

        foreach ($catalog as $key => $definition) {
            if (!empty($definition['feature']) && !TenantSettings::enabled($definition['feature'], $tenantId)) {
                $rules[$key] = 'hidden';

                continue;
            }

            $rule = $saved[$key] ?? ($definition['default'] ?? 'optional');
            $rules[$key] = in_array($rule, ['required', 'optional', 'hidden'], true) ? $rule : ($definition['default'] ?? 'optional');
        }

        return $rules;
    }

    public function isVisible(string $form, string $field, ?int $tenantId = null): bool
    {
        return ($this->resolvedRules($form, $tenantId)[$field] ?? 'optional') !== 'hidden';
    }

    public function isRequired(string $form, string $field, ?int $tenantId = null): bool
    {
        return ($this->resolvedRules($form, $tenantId)[$field] ?? 'optional') === 'required';
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string|array<int, string>>
     */
    public function customerValidationRules(?int $tenantId = null, array $context = []): array
    {
        $rules = $this->resolvedRules('customer', $tenantId);
        $validation = [];

        $map = [
            'name' => 'required|string|max:255',
            'tablo' => 'required|string|max:255',
            'customer_code' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'mobile' => 'required|string|max:30',
            'national_id' => 'required|string|max:20',
            'mapcode' => 'nullable|string|max:100',
            'customer_group_id' => 'required|integer|min:1',
            'sales_channel_id' => 'required|integer|min:1',
            'customer_status_id' => 'nullable|integer|min:0',
            'region_id' => 'nullable|integer|min:0',
            'area' => 'nullable|integer|min:0',
            'address' => 'required|string|max:2000',
            'store_address' => 'nullable|string|max:2000',
            'shop_lat' => 'nullable|string|max:50',
            'shop_lng' => 'nullable|string|max:50',
            'store_lat' => 'nullable|string|max:50',
            'store_lng' => 'nullable|string|max:50',
        ];

        foreach ($map as $field => $baseRule) {
            if (($rules[$field] ?? 'optional') === 'hidden') {
                continue;
            }

            if (($rules[$field] ?? 'optional') === 'required') {
                $validation[$field] = str_replace('nullable|', '', str_replace('nullable', 'required', $baseRule));

                continue;
            }

            $validation[$field] = str_replace('required|', 'nullable|', $baseRule);
        }

        if (!empty($context['requiresAreaWorkflow']) && $this->isVisible('customer', 'region_id', $tenantId)) {
            $validation['region_id'] = 'required|integer|min:1';
        }

        if (!empty($context['requiresRouteWorkflow']) && $this->isVisible('customer', 'area', $tenantId)) {
            $validation['area'] = 'required|integer|min:1';
        }

        return $validation;
    }

    /**
     * @throws ValidationException
     */
    public function validateCustomerRequest(array $data, ?int $tenantId = null, array $context = []): array
    {
        return Validator::make($data, $this->customerValidationRules($tenantId, $context))->validate();
    }

    /**
     * @return array<int, string>
     */
    public function requiredFieldKeys(string $form, ?int $tenantId = null): array
    {
        return array_keys(array_filter(
            $this->resolvedRules($form, $tenantId),
            fn (string $rule) => $rule === 'required'
        ));
    }

    /**
     * @return array<int, string>
     */
    public function optionalFieldKeys(string $form, ?int $tenantId = null): array
    {
        return array_keys(array_filter(
            $this->resolvedRules($form, $tenantId),
            fn (string $rule) => $rule === 'optional'
        ));
    }

    /**
     * @param  array<string, string>  $input
     * @return array<string, string>
     */
    public function normalizeSavedRules(string $form, array $input): array
    {
        $catalog = $form === 'invoice' ? $this->invoiceFieldCatalog() : $this->customerFieldCatalog();
        $normalized = [];

        foreach ($catalog as $key => $definition) {
            $value = $input[$key] ?? ($definition['default'] ?? 'optional');
            $normalized[$key] = in_array($value, ['required', 'optional', 'hidden'], true)
                ? $value
                : ($definition['default'] ?? 'optional');
        }

        return $normalized;
    }
}
