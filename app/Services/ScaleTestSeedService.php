<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScaleTestSeedService
{
    public function marker(): string
    {
        return (string) config('erp_scale.load_test.marker', 'STSCALE');
    }

    public function counts(?array $overrides = null): array
    {
        $defaults = (array) config('erp_scale.load_test.counts', []);

        return [
            'customers' => (int) ($overrides['customers'] ?? $defaults['customers'] ?? 10000),
            'products' => (int) ($overrides['products'] ?? $defaults['products'] ?? 5000),
            'pishfactors' => (int) ($overrides['pishfactors'] ?? $defaults['pishfactors'] ?? 50000),
        ];
    }

    public function purge(): array
    {
        $marker = $this->marker();
        $deleted = ['customers' => 0, 'products' => 0, 'pishfactors' => 0];

        if (Schema::hasTable('pishfactors')) {
            $deleted['pishfactors'] = DB::table('pishfactors')
                ->where('tozihat', 'like', $marker . '%')
                ->delete();
        }

        if (Schema::hasTable('products')) {
            $deleted['products'] = DB::table('products')
                ->where(function ($query) use ($marker) {
                    $query->where('title', 'like', $marker . '%');
                    if (Schema::hasColumn('products', 'sku')) {
                        $query->orWhere('sku', 'like', $marker . '%');
                    }
                })
                ->delete();
        }

        if (Schema::hasTable('customers')) {
            $deleted['customers'] = DB::table('customers')
                ->where('customer_code', 'like', $marker . '%')
                ->delete();
        }

        return $deleted;
    }

    public function seed(?array $overrides = null): array
    {
        $counts = $this->counts($overrides);
        $context = $this->context();
        $marker = $this->marker();
        $chunk = max(100, (int) config('erp_scale.load_test.chunk_size', 500));
        $now = now();

        $customerIds = $this->seedCustomers($counts['customers'], $context, $marker, $chunk, $now);
        $productIds = $this->seedProducts($counts['products'], $context, $marker, $chunk, $now);
        $invoiceCount = $this->seedPishfactors($counts['pishfactors'], $context, $marker, $customerIds, $chunk, $now);

        return [
            'tenant_id' => $context['tenant_id'],
            'organization_id' => $context['organization_id'],
            'counts' => $counts,
            'inserted' => [
                'customers' => count($customerIds),
                'products' => count($productIds),
                'pishfactors' => $invoiceCount,
            ],
        ];
    }

    private function context(): array
    {
        $tenantId = (int) config('erp_scale.load_test.tenant_id', 1);
        $user = User::query()->where('isGod', 1)->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        if (!$user) {
            throw new \RuntimeException('No user available for scale test seed context.');
        }

        $product = DB::table('products')->orderBy('id')->first();

        return [
            'tenant_id' => $tenantId,
            'organization_id' => $this->resolveOrganizationId($user),
            'user_id' => (int) $user->id,
            'product_template' => $product ? (array) $product : null,
        ];
    }

    private function resolveOrganizationId(User $user): int
    {
        $organizationId = $user->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded) && isset($decoded[0])) {
            return (int) $decoded[0];
        }

        return (int) ($organizationId ?: 1);
    }

    private function seedCustomers(int $count, array $context, string $marker, int $chunk, $now): array
    {
        if (!Schema::hasTable('customers') || $count <= 0) {
            return [];
        }

        $ids = [];
        $buffer = [];

        for ($i = 1; $i <= $count; $i++) {
            $row = [
                'name' => $marker . ' Customer ' . $i,
                'national_id' => str_pad((string) ($i % 999999999999), 12, '0', STR_PAD_LEFT),
                'phone' => '021' . str_pad((string) ($i % 9999999), 7, '0', STR_PAD_LEFT),
                'mobile' => '09' . str_pad((string) (100000000 + ($i % 899999999)), 9, '0', STR_PAD_LEFT),
                'customer_code' => $marker . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'status' => 1,
                'region_id' => 1,
                'area' => 1,
                'address' => 'Scale test address ' . $i,
                'organization_id' => $context['organization_id'],
                'created_by' => $context['user_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('customers', 'tenant_id')) {
                $row['tenant_id'] = $context['tenant_id'];
            }

            $buffer[] = $row;

            if (count($buffer) >= $chunk) {
                DB::table('customers')->insert($buffer);
                $ids = array_merge($ids, $this->pluckIds('customers', 'customer_code', array_column($buffer, 'customer_code')));
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('customers')->insert($buffer);
            $ids = array_merge($ids, $this->pluckIds('customers', 'customer_code', array_column($buffer, 'customer_code')));
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function seedProducts(int $count, array $context, string $marker, int $chunk, $now): array
    {
        if (!Schema::hasTable('products') || $count <= 0 || empty($context['product_template'])) {
            return [];
        }

        $template = $context['product_template'];
        unset($template['id']);
        $ids = [];
        $buffer = [];

        for ($i = 1; $i <= $count; $i++) {
            $row = $template;
            $row['title'] = $marker . ' Product ' . $i;
            $row['display_name'] = $row['title'];
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $row['deleted_at'] = null;

            if (Schema::hasColumn('products', 'sku')) {
                $row['sku'] = $marker . '-' . $i;
            }

            if (Schema::hasColumn('products', 'tenant_id')) {
                $row['tenant_id'] = $context['tenant_id'];
            }

            $buffer[] = $row;

            if (count($buffer) >= $chunk) {
                DB::table('products')->insert($buffer);
                $ids = array_merge($ids, $this->pluckIds('products', 'title', array_column($buffer, 'title')));
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('products')->insert($buffer);
            $ids = array_merge($ids, $this->pluckIds('products', 'title', array_column($buffer, 'title')));
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function pluckIds(string $table, string $column, array $values): array
    {
        return DB::table($table)->whereIn($column, $values)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedPishfactors(int $count, array $context, string $marker, array $customerIds, int $chunk, $now): int
    {
        if (!Schema::hasTable('pishfactors') || $count <= 0) {
            return 0;
        }

        if ($customerIds === []) {
            $customerIds = DB::table('customers')
                ->where('customer_code', 'like', $marker . '%')
                ->orderBy('id')
                ->pluck('id')
                ->all();
        }

        if ($customerIds === []) {
            return 0;
        }

        $customerCount = count($customerIds);
        $baseInvoice = (int) config('erp_scale.load_test.invoice_id_base', 900000000);
        $inserted = 0;
        $buffer = [];

        for ($i = 1; $i <= $count; $i++) {
            $row = [
                'customer_id' => $customerIds[($i - 1) % $customerCount],
                'visitor_id' => $context['user_id'],
                'sarparast_id' => $context['user_id'],
                'invoiceID' => $baseInvoice + $i,
                'status' => 1,
                'step' => 0,
                'pat_price' => (string) random_int(10000, 500000),
                'fullPrice' => (string) random_int(10000, 500000),
                'tozihat' => $marker . ' invoice ' . $i,
                'organization_id' => $context['organization_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('pishfactors', 'tenant_id')) {
                $row['tenant_id'] = $context['tenant_id'];
            }

            if (Schema::hasColumn('pishfactors', 'tenants_id')) {
                $row['tenants_id'] = $context['tenant_id'];
            }

            if (Schema::hasColumn('pishfactors', 'recive_date_en')) {
                $row['recive_date_en'] = $now;
            }

            $buffer[] = $row;

            if (count($buffer) >= $chunk) {
                DB::table('pishfactors')->insert($buffer);
                $inserted += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('pishfactors')->insert($buffer);
            $inserted += count($buffer);
        }

        return $inserted;
    }
}
