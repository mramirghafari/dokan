<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\Product;
use App\Models\User;

class OrderDiscountLimitService
{
    public function isSubscriptionDurationProduct(Product $product): bool
    {
        return $product->resolveOrderQuantityMode() === 'none' && $product->usesDurationEncodedQuantity();
    }

    /**
     * @return array{pack: int, tedad: int}
     */
    public function subscriptionLineQuantities(Product $product): array
    {
        return $product->fixedOrderQuantities();
    }

    /**
     * @return array{pack: int, tedad: int}
     */
    public function resolveSubmittedQuantities(Product $product, int $pack, int $tedad): array
    {
        if ($product->resolveOrderQuantityMode() === 'none') {
            return $product->fixedOrderQuantities();
        }

        return [
            'pack' => $pack,
            'tedad' => $tedad,
        ];
    }

    /**
     * @return array{
     *     max_discount_percent: ?float,
     *     max_discount_amount: ?float,
     *     max_purchase_amount: ?float
     * }
     */
    public function effectiveLimits(User $user, Product $product, ?Customers $customer = null): array
    {
        $percentLimits = [];
        $amountLimits = [];

        $productPercent = $this->nullableFloat($product->discount);
        if ($productPercent !== null && $productPercent > 0) {
            $percentLimits[] = $productPercent;
        }

        $productAmount = $this->nullableFloat($product->max_discount_amount ?? null);
        if ($productAmount !== null && $productAmount > 0) {
            $amountLimits[] = $productAmount;
        }

        foreach ($user->roles ?? [] as $role) {
            $rolePercent = $this->nullableFloat($role->max_discount_percent ?? null);
            if ($rolePercent !== null && $rolePercent > 0) {
                $percentLimits[] = $rolePercent;
            }

            $roleAmount = $this->nullableFloat($role->max_discount_amount ?? null);
            if ($roleAmount !== null && $roleAmount > 0) {
                $amountLimits[] = $roleAmount;
            }
        }

        if ($customer) {
            $customerAmount = $this->nullableFloat($customer->max_discount_amount ?? null);
            if ($customerAmount !== null && $customerAmount > 0) {
                $amountLimits[] = $customerAmount;
            }
        }

        return [
            'max_discount_percent' => empty($percentLimits) ? null : min($percentLimits),
            'max_discount_amount' => empty($amountLimits) ? null : min($amountLimits),
            'max_purchase_amount' => $customer
                ? $this->nullableFloat($customer->max_purchase_amount ?? null)
                : null,
        ];
    }

    public function validateLine(
        User $user,
        Product $product,
        ?Customers $customer,
        float $discountPercent,
        int $lineGrossRials
    ): ?string {
        if ($discountPercent <= 0) {
            return null;
        }

        $limits = $this->effectiveLimits($user, $product, $customer);
        $discountAmount = (int) round(($lineGrossRials * $discountPercent) / 100);

        if ($limits['max_discount_percent'] !== null && $discountPercent > $limits['max_discount_percent']) {
            return sprintf(
                'تخفیف «%s» (%s%%) از سقف مجاز (%s%%) بیشتر است.',
                $product->title,
                $this->formatNumber($discountPercent),
                $this->formatNumber($limits['max_discount_percent'])
            );
        }

        if ($limits['max_discount_amount'] !== null && $discountAmount > $limits['max_discount_amount']) {
            return sprintf(
                'مبلغ تخفیف «%s» (%s ریال) از سقف مجاز (%s ریال) بیشتر است.',
                $product->title,
                number_format($discountAmount),
                number_format((int) $limits['max_discount_amount'])
            );
        }

        return null;
    }

    public function validatePurchaseAmount(?Customers $customer, int $orderTotalRials): ?string
    {
        if (!$customer) {
            return null;
        }

        $maxPurchase = $this->nullableFloat($customer->max_purchase_amount ?? null);
        if ($maxPurchase === null || $maxPurchase <= 0) {
            return null;
        }

        if ($orderTotalRials > $maxPurchase) {
            return sprintf(
                'مبلغ سفارش (%s ریال) از سقف مجاز خرید مشتری (%s ریال) بیشتر است.',
                number_format($orderTotalRials),
                number_format((int) $maxPurchase)
            );
        }

        return null;
    }

    public function lineGrossRials(Product $product, int $pack, int $tedad, int $unitPrice): int
    {
        $packItems = max(1, (int) $product->pack_items);
        $quantity = ($pack * $packItems) + $tedad;

        return $quantity * $unitPrice;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
