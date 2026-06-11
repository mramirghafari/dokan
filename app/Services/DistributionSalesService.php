<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\DistributionMobileOrder;
use App\Models\DistributionPromotion;
use App\Models\DistributionSyncQueue;
use App\Models\DistributionVisitPlan;
use App\Models\DistributionVisitStop;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DistributionSalesService
{
    public function __construct(private NumberingService $numberingService) {}

    public function createVisitPlan(array $payload, User $user): DistributionVisitPlan
    {
        return DB::transaction(function () use ($payload, $user) {
            $date = Arr::get($payload, 'planned_date_en') ?: now()->toDateString();
            $visitorId = (int) Arr::get($payload, 'visitor_id', $user->id);
            $tenantId = $this->tenantId($user);
            $organizationId = $this->organizationId($user);
            $customerIds = $this->customerIdsForPlan($payload, $user);

            if (empty($customerIds)) {
                throw ValidationException::withMessages(['customers' => 'برای برنامه ویزیت حداقل یک مشتری لازم است.']);
            }

            $plan = DistributionVisitPlan::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'visitor_id' => $visitorId,
                'plan_number' => Arr::get($payload, 'plan_number') ?: $this->numberingService->nextDocumentNumber('distribution_visit_plan', 'DVP', $tenantId, $organizationId, $date),
                'title' => Arr::get($payload, 'title') ?: 'برنامه ویزیت ' . verta($date)->format('Y/m/d'),
                'route_code' => Arr::get($payload, 'route_code'),
                'area_id' => Arr::get($payload, 'area_id'),
                'region_id' => Arr::get($payload, 'region_id'),
                'planned_date_en' => $date,
                'planned_date_fa' => $this->jalaliDate($date),
                'sales_mode' => Arr::get($payload, 'sales_mode', 'hot_sale'),
                'status' => Arr::get($payload, 'status', 'planned'),
                'planned_customers_count' => count($customerIds),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user->id,
            ]);

            foreach (array_values($customerIds) as $index => $customerId) {
                $plan->stops()->create([
                    'customer_id' => $customerId,
                    'sequence' => $index + 1,
                    'visit_status' => 'planned',
                    'planned_at' => Arr::get($payload, 'planned_at'),
                ]);
            }

            return $this->refreshPlanCounters($plan);
        });
    }

    public function checkInStop(DistributionVisitStop $stop, array $payload, User $user): DistributionVisitStop
    {
        return DB::transaction(function () use ($stop, $payload, $user) {
            $stop = DistributionVisitStop::whereKey($stop->id)->lockForUpdate()->firstOrFail();
            $this->authorizeStopUser($stop, $user);

            $stop->update([
                'visit_status' => 'visited',
                'checked_in_at' => $stop->checked_in_at ?: now(),
                'lat' => Arr::get($payload, 'lat'),
                'lng' => Arr::get($payload, 'lng'),
                'client_uid' => Arr::get($payload, 'client_uid', $stop->client_uid),
                'extra_json' => array_filter(['check_in_note' => Arr::get($payload, 'note')]),
            ]);

            $this->refreshPlanCounters($stop->plan);

            return $stop->fresh(['plan', 'customer', 'pishfactor']) ?: $stop;
        });
    }

    public function markNoOrder(DistributionVisitStop $stop, array $payload, User $user): DistributionVisitStop
    {
        return DB::transaction(function () use ($stop, $payload, $user) {
            $stop = DistributionVisitStop::whereKey($stop->id)->lockForUpdate()->firstOrFail();
            $this->authorizeStopUser($stop, $user);

            $stop->update([
                'visit_status' => 'no_order',
                'checked_in_at' => $stop->checked_in_at ?: now(),
                'checked_out_at' => now(),
                'no_order_reason' => Arr::get($payload, 'reason'),
                'lat' => Arr::get($payload, 'lat', $stop->lat),
                'lng' => Arr::get($payload, 'lng', $stop->lng),
                'client_uid' => Arr::get($payload, 'client_uid', $stop->client_uid),
            ]);

            $this->recordSync($user, Arr::get($payload, 'client_uid', 'no-order-' . $stop->id . '-' . now()->timestamp), 'visit_stop', $stop->id, 'no_order', $payload, 'processed');
            $this->refreshPlanCounters($stop->plan);

            return $stop->fresh(['plan', 'customer', 'pishfactor']) ?: $stop;
        });
    }

    public function createMobileOrder(array $payload, User $user): DistributionMobileOrder
    {
        return DB::transaction(function () use ($payload, $user) {
            $clientUid = Arr::get($payload, 'client_order_uid');
            $tenantId = $this->tenantId($user);

            if ($clientUid) {
                $existing = DistributionMobileOrder::where('tenant_id', $tenantId)->where('client_order_uid', $clientUid)->first();
                if ($existing) {
                    return $existing->fresh(['pishfactor.items.product', 'stop', 'plan']) ?: $existing;
                }
            }

            $customer = Customers::findOrFail((int) Arr::get($payload, 'customer_id'));
            $stop = Arr::get($payload, 'visit_stop_id') ? DistributionVisitStop::find((int) Arr::get($payload, 'visit_stop_id')) : null;

            if ($stop) {
                $this->authorizeStopUser($stop, $user);
            }

            $rows = $this->normalizeItems(Arr::get($payload, 'items', []));
            $totals = $this->totals($rows, $this->activePromotions($user));
            $organizationId = $customer->organization_id ?: $this->organizationId($user);
            $date = Arr::get($payload, 'offline_created_at') ?: now()->toDateTimeString();
            $invoiceId = $this->nextInvoiceId();

            $factor = Pishfactor::create([
                'customer_id' => $customer->id,
                'visitor_id' => $user->id,
                'sarparast_id' => Arr::get($payload, 'sarparast_id', $user->id),
                'updated_by' => $user->id,
                'payment_type' => (int) Arr::get($payload, 'payment_method', 3),
                'recive_date' => $this->jalaliDate($date),
                'recive_date_en' => $date,
                'invoiceID' => $invoiceId,
                'mobile_order_uid' => $clientUid,
                'distribution_order_type' => Arr::get($payload, 'order_type', 'hot_sale'),
                'sale_mode' => Arr::get($payload, 'sale_mode', 'field'),
                'visit_stop_id' => $stop?->id,
                'promotion_discount_amount' => $totals['discount_amount'],
                'offline_created_at' => Arr::get($payload, 'offline_created_at'),
                'sync_status' => Arr::get($payload, 'sync_status', 'synced'),
                'sales_document_type' => Arr::get($payload, 'order_type') === 'return' ? 'sales_return_request' : 'sales_order',
                ...app(SalesScenarioService::class)->initialInvoicePayload($user, $tenantId, [
                    'amount' => $totals['net_amount'],
                    'discount_percent' => $totals['gross_amount'] > 0
                        ? round(($totals['discount_amount'] / $totals['gross_amount']) * 100, 2)
                        : 0,
                ]),
                'settlement_status' => 'unsettled',
                'reserve_status' => 'not_reserved',
                'warehouse_issue_status' => 'pending',
                'step' => 0,
                'pat_price' => (string) $totals['gross_amount'],
                'fullPrice' => (string) $totals['net_amount'],
                'tozihat' => Arr::get($payload, 'description'),
                'organization_id' => $organizationId,
                'tenant_id' => $tenantId,
                'tenants_id' => $tenantId,
                'area_id' => $customer->area ?: 0,
                'region_id' => $customer->region_id ?: 0,
                'create_lat' => Arr::get($payload, 'lat'),
                'create_lng' => Arr::get($payload, 'lng'),
            ]);

            foreach ($rows as $row) {
                PishFactorItems::create([
                    'pishfactor_id' => $factor->id,
                    'tenant_id' => $tenantId,
                    'pr_id' => $row['product_id'],
                    'unit_id' => $row['unit_id'],
                    'pack' => $row['pack'],
                    'tedad' => $row['quantity'],
                    'price' => $row['price'],
                    'discount' => $row['discount_percent'],
                    'discount_amount' => $row['discount_amount'],
                    'tax_amount' => $row['tax_amount'],
                    'line_total' => $row['line_total'],
                    'reserved_quantity' => 0,
                ]);
            }

            if ($stop) {
                $stop->update([
                    'pishfactor_id' => $factor->id,
                    'visit_status' => 'order_created',
                    'checked_in_at' => $stop->checked_in_at ?: now(),
                    'checked_out_at' => now(),
                    'collection_amount' => $this->money(Arr::get($payload, 'collection_amount', 0)),
                    'lat' => Arr::get($payload, 'lat', $stop->lat),
                    'lng' => Arr::get($payload, 'lng', $stop->lng),
                    'client_uid' => $clientUid,
                ]);

                $this->refreshPlanCounters($stop->plan);
            }

            $mobileOrder = DistributionMobileOrder::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'pishfactor_id' => $factor->id,
                'distribution_visit_plan_id' => $stop?->distribution_visit_plan_id,
                'distribution_visit_stop_id' => $stop?->id,
                'visitor_id' => $user->id,
                'customer_id' => $customer->id,
                'client_order_uid' => $clientUid,
                'order_type' => Arr::get($payload, 'order_type', 'hot_sale'),
                'sale_mode' => Arr::get($payload, 'sale_mode', 'field'),
                'payment_method' => (int) Arr::get($payload, 'payment_method', 3),
                'gross_amount' => $totals['gross_amount'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'net_amount' => $totals['net_amount'],
                'location_lat' => Arr::get($payload, 'lat'),
                'location_lng' => Arr::get($payload, 'lng'),
                'offline_payload_json' => $payload,
                'offline_created_at' => Arr::get($payload, 'offline_created_at'),
                'sync_status' => Arr::get($payload, 'sync_status', 'synced'),
                'synced_at' => now(),
            ]);

            $this->recordSync($user, $clientUid ?: 'order-' . $mobileOrder->id, 'mobile_order', $mobileOrder->id, 'create_order', $payload, 'processed');

            return $mobileOrder->fresh(['pishfactor.items.product', 'stop.customer', 'plan']) ?: $mobileOrder;
        });
    }

    public function syncPush(array $payload, User $user): array
    {
        $results = [];

        foreach (Arr::get($payload, 'items', []) as $item) {
            $entityType = Arr::get($item, 'entity_type');
            $clientUid = Arr::get($item, 'client_uid');
            $sync = $this->recordSync($user, $clientUid, $entityType, null, Arr::get($item, 'action', 'upsert'), $item, 'pending');

            try {
                if ($entityType === 'mobile_order') {
                    $order = $this->createMobileOrder(Arr::get($item, 'payload', []), $user);
                    $sync->update(['status' => 'processed', 'entity_id' => $order->id, 'processed_at' => now()]);
                    $results[] = ['client_uid' => $clientUid, 'status' => 'processed', 'entity_id' => $order->id, 'pishfactor_id' => $order->pishfactor_id];
                } elseif ($entityType === 'visit_no_order') {
                    $stop = DistributionVisitStop::findOrFail((int) Arr::get($item, 'payload.visit_stop_id'));
                    $this->markNoOrder($stop, Arr::get($item, 'payload', []), $user);
                    $sync->update(['status' => 'processed', 'entity_id' => $stop->id, 'processed_at' => now()]);
                    $results[] = ['client_uid' => $clientUid, 'status' => 'processed', 'entity_id' => $stop->id];
                } else {
                    $sync->update(['status' => 'conflict', 'conflict_reason' => 'نوع sync پشتیبانی نمی شود.']);
                    $results[] = ['client_uid' => $clientUid, 'status' => 'conflict', 'message' => 'unsupported_entity_type'];
                }
            } catch (\Throwable $exception) {
                $sync->update(['status' => 'conflict', 'attempts' => $sync->attempts + 1, 'conflict_reason' => $exception->getMessage()]);
                $results[] = ['client_uid' => $clientUid, 'status' => 'conflict', 'message' => $exception->getMessage()];
            }
        }

        return $results;
    }

    public function activePromotions(User $user)
    {
        $today = now()->toDateString();

        return DistributionPromotion::query()
            ->where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
            })
            ->when($this->tenantId($user), fn($query, $tenantId) => $query->where(function ($scope) use ($tenantId) {
                $scope->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            }))
            ->latest('id')
            ->get();
    }

    private function normalizeItems(array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $product = Product::find((int) Arr::get($item, 'product_id'));
            if (!$product) {
                continue;
            }

            $pack = $this->quantity(Arr::get($item, 'pack', 0));
            $quantity = $this->quantity(Arr::get($item, 'quantity', Arr::get($item, 'tedad', 0)));

            if ($pack <= 0 && $quantity <= 0) {
                continue;
            }

            $price = $this->money(Arr::get($item, 'price', $product->price ?: 0));
            $lineQuantity = $pack > 0 ? $pack * max(1, (float) ($product->pack_items ?: 1)) + $quantity : $quantity;
            $gross = round($lineQuantity * $price, 2);
            $discountAmount = $this->money(Arr::get($item, 'discount_amount', 0));
            $taxAmount = $this->money(Arr::get($item, 'tax_amount', 0));
            $lineTotal = round(max(0, $gross - $discountAmount + $taxAmount), 2);

            $rows[] = [
                'product_id' => $product->id,
                'unit_id' => Arr::get($item, 'unit_id', $product->base_unit_id),
                'pack' => $pack,
                'quantity' => $quantity,
                'line_quantity' => $lineQuantity,
                'price' => $price,
                'discount_percent' => $this->quantity(Arr::get($item, 'discount_percent', 0)),
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'gross_amount' => $gross,
                'line_total' => $lineTotal,
            ];
        }

        if (empty($rows)) {
            throw ValidationException::withMessages(['items' => 'برای ثبت سفارش حداقل یک ردیف کالا لازم است.']);
        }

        return $rows;
    }

    private function totals(array &$rows, $promotions): array
    {
        $gross = round(array_sum(array_column($rows, 'gross_amount')), 2);
        $manualDiscount = round(array_sum(array_column($rows, 'discount_amount')), 2);
        $tax = round(array_sum(array_column($rows, 'tax_amount')), 2);
        $promotionDiscount = 0;

        foreach ($promotions as $promotion) {
            if ((float) $promotion->min_order_amount > 0 && $gross < (float) $promotion->min_order_amount) {
                continue;
            }

            if ((int) $promotion->max_uses > 0 && (int) $promotion->used_count >= (int) $promotion->max_uses) {
                continue;
            }

            if ($promotion->product_id && !collect($rows)->contains(fn($row) => (int) $row['product_id'] === (int) $promotion->product_id)) {
                continue;
            }

            $promotionDiscount += match ($promotion->promotion_type) {
                'discount_amount' => (float) $promotion->discount_amount,
                default => round($gross * (float) $promotion->discount_percent / 100, 2),
            };
        }

        $discount = min($gross, round($manualDiscount + $promotionDiscount, 2));

        return [
            'gross_amount' => $gross,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'net_amount' => round(max(0, $gross - $discount + $tax), 2),
        ];
    }

    private function customerIdsForPlan(array $payload, User $user): array
    {
        $customerIds = array_filter(array_map('intval', Arr::wrap(Arr::get($payload, 'customer_ids', []))));

        if (!empty($customerIds)) {
            return Customers::forOrganizations($user)->whereIn('id', $customerIds)->pluck('id')->all();
        }

        return Customers::forOrganizations($user)
            ->when(Arr::get($payload, 'area_id'), fn($query, $areaId) => $query->where('area', $areaId))
            ->when(Arr::get($payload, 'region_id'), fn($query, $regionId) => $query->where('region_id', $regionId))
            ->where('status', 1)
            ->orderBy('id')
            ->limit((int) Arr::get($payload, 'limit', 50))
            ->pluck('id')
            ->all();
    }

    private function refreshPlanCounters(?DistributionVisitPlan $plan): DistributionVisitPlan
    {
        if (!$plan) {
            throw ValidationException::withMessages(['visit_plan' => 'برنامه ویزیت پیدا نشد.']);
        }

        $stops = $plan->stops()->get();
        $plan->update([
            'planned_customers_count' => $stops->count(),
            'visited_count' => $stops->whereIn('visit_status', ['visited', 'order_created', 'no_order'])->count(),
            'ordered_count' => $stops->where('visit_status', 'order_created')->count(),
            'no_order_count' => $stops->where('visit_status', 'no_order')->count(),
            'collected_amount' => round((float) $stops->sum('collection_amount'), 2),
            'status' => $stops->count() > 0 && $stops->where('visit_status', 'planned')->count() === 0 ? 'completed' : $plan->status,
        ]);

        return $plan->fresh(['visitor', 'stops.customer', 'stops.pishfactor']) ?: $plan;
    }

    private function authorizeStopUser(DistributionVisitStop $stop, User $user): void
    {
        $stop->loadMissing('plan');

        if ($stop->plan && (int) $user->isGod !== 1 && (int) $stop->plan->visitor_id !== (int) $user->id) {
            throw ValidationException::withMessages(['visit_stop' => 'این توقف ویزیت به کاربر فعلی اختصاص ندارد.']);
        }
    }

    private function recordSync(User $user, ?string $clientUid, string $entityType, ?int $entityId, string $action, array $payload, string $status): DistributionSyncQueue
    {
        return DistributionSyncQueue::updateOrCreate(
            ['user_id' => $user->id, 'client_uid' => $clientUid ?: uniqid('sync-', true)],
            [
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'payload_json' => $payload,
                'status' => $status,
                'received_at' => now(),
                'processed_at' => $status === 'processed' ? now() : null,
            ]
        );
    }

    private function nextInvoiceId(): int
    {
        return ((int) Pishfactor::query()->max('invoiceID')) + 1;
    }

    private function tenantId(User $user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id;
    }

    private function organizationId(User $user): ?int
    {
        return $user->organization_id ?: null;
    }

    private function jalaliDate($date): string
    {
        try {
            return verta($date ?: now())->format('Y/m/d');
        } catch (\Throwable $exception) {
            return verta()->format('Y/m/d');
        }
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function quantity($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 3);
    }
}
