<?php



namespace App\Services;



use App\Models\Brand;

use App\Models\Category;

use App\Models\DataExchangeRun;

use App\Models\Organization;

use App\Models\Product;
use App\Models\ProductPricePeriod;

use App\Models\Store;

use App\Models\Unit;

use App\Models\User;

use App\Scopes\TenantScope;

use Illuminate\Support\Facades\Validator;



class ProductBulkImportService

{

    public const TEMPLATE_FILENAME = 'products-import-template-v1.csv';



    private const TITLE_ALIASES = ['title', 'عنوان', 'عنوان محصول', 'نام محصول', 'نام کالا'];

    private const DISPLAY_NAME_ALIASES = ['display_name', 'نام نمایشی'];

    private const SKU_ALIASES = ['sku', 'کد محصول', 'کد کالا', 'کد'];

    private const PARENT_CATEGORY_ALIASES = ['parentCategory_id', 'parent_category', 'دسته‌بندی', 'دسته بندی', 'دسته'];

    private const CHILD_CATEGORY_ALIASES = ['childCategory_id', 'child_category', 'زیردسته', 'زیر دسته'];

    private const PRODUCT_TYPE_ALIASES = ['product_type', 'نوع کالا', 'نوع'];

    private const STOCK_TRACKING_ALIASES = ['stock_tracking_mode', 'کنترل موجودی'];

    private const VALUATION_ALIASES = ['valuation_method', 'روش ارزش‌گذاری', 'روش ارزش گذاری'];

    private const BASE_UNIT_ALIASES = ['base_unit_id', 'pr_unit', 'واحد اصلی', 'واحد'];

    private const SECONDARY_UNIT_ALIASES = ['secondary_unit_id', 'pr_sub_unit', 'واحد فرعی'];

    private const CONVERSION_ALIASES = ['unit_conversion_factor', 'pack_items', 'ضریب تبدیل'];
    private const PR_WEIGHT_ALIASES = ['pr_weight', 'وزن واحد اصلی'];
    private const PR_WEIGHT_UNIT_ALIASES = ['pr_weight_unit', 'واحد وزن اصلی', 'واحد اصلی باربری'];
    private const PACK_WEIGHT_ALIASES = ['pack_weight', 'وزن واحد فرعی'];
    private const PACK_WEIGHT_UNIT_ALIASES = ['pack_weight_unit', 'واحد وزن فرعی', 'واحد فرعی باربری'];

    private const ORDER_QTY_MODE_ALIASES = ['order_quantity_mode', 'نحوه فروش در سفارش', 'نحوه سفارش در ثبت سفارش'];

    private const PRICE_ALIASES = ['price', 'قیمت فروش', 'قیمت'];

    private const PURCHASE_PRICE_ALIASES = ['purchase_price', 'قیمت خرید'];

    private const COST_PRICE_ALIASES = ['cost_price', 'قیمت تمام‌شده', 'قیمت تمام شده'];

    private const REPRESENTATIVE_PRICE_ALIASES = ['representative_price', 'قیمت نماینده'];

    private const WHOLESALE_PRICE_ALIASES = ['wholesale_price', 'قیمت عمده'];

    private const CONSUMER_PRICE_ALIASES = ['consumer_price', 'fee_masraf', 'قیمت مصرف‌کننده', 'قیمت مصرف کننده'];

    private const DISCOUNT_ALIASES = ['discount', 'حداکثر درصد تخفیف', 'درصد تخفیف'];

    private const MAX_DISCOUNT_AMOUNT_ALIASES = ['max_discount_amount', 'حداکثر مبلغ تخفیف'];

    private const TAX_ALIASES = ['tax', 'مالیات'];

    private const BRAND_ALIASES = ['brand_id', 'brand', 'برند'];

    private const DESCRIPTION_ALIASES = ['description', 'توضیحات'];

    private const ORGANIZATION_ALIASES = ['organization_id', 'organization', 'واحد پخش', 'سازمان'];

    private const STORE_ALIASES = ['store_id', 'store', 'انبار'];

    private const ENTITY_ALIASES = ['entity', 'موجودی اولیه', 'موجودی'];

    private const ORDER_LIMIT_ALIASES = ['orderLimit', 'order_limit', 'محدودیت سفارش'];

    private const IS_ACTIVE_ALIASES = ['isActive', 'is_active', 'وضعیت فعال', 'فعال'];

    private const IS_MATERIAL_ALIASES = ['isMaterial', 'is_material', 'مواد اولیه'];
    private const PRICE_RANGE_TYPE_ALIASES = ['price_range_type', 'بازه قیمت نوع', 'نوع بازه قیمت'];
    private const PRICE_RANGE_AMOUNT_ALIASES = ['price_range_amount', 'بازه قیمت مبلغ', 'مبلغ بازه قیمت'];
    private const PRICE_RANGE_STARTS_AT_ALIASES = ['price_range_starts_at', 'بازه قیمت از تاریخ', 'از تاریخ بازه قیمت'];
    private const PRICE_RANGE_ENDS_AT_ALIASES = ['price_range_ends_at', 'بازه قیمت تا تاریخ', 'تا تاریخ بازه قیمت'];



    public function __construct(
        private TenantContextService $tenantContext,
        private ProductPricePeriodService $pricePeriodService
    ) {}



    /**

     * @param  array<int, array<string, mixed>>  $rows

     */

    public function validateImportStructure(array $rows): void

    {

        if ($rows === []) {

            throw new \RuntimeException('فایل خالی است یا سرستون داده پیدا نشد.');

        }



        $firstRow = $rows[0];



        if ($this->looksLikeNumericRowKeys(array_keys($firstRow))) {

            throw new \RuntimeException(

                'سطر اول فایل سرستون معتبر ندارد. احتمالاً فایل با قالب ورود داده هم‌خوان نیست یا یک ردیف عنوان بالای جدول وجود دارد.'

            );

        }



        if (!$this->rowHasMappableProductColumn($firstRow)) {

            $headers = $this->formatDetectedHeaders($firstRow);



            throw new \RuntimeException(

                'ستون «عنوان» یا «SKU» در سرستون فایل یافت نشد.'

                . ($headers ? " ستون‌های تشخیص‌داده‌شده: {$headers}." : '')

                . ' لطفاً فایل را مطابق قالب CSV همین صفحه آماده کنید.'

            );

        }

    }



    /**

     * @param  array<int, array<string, mixed>>  $rows

     */

    public function buildResultMessage(array $summary, array $rows = []): string

    {

        $success = (int) $summary['created'] + (int) $summary['updated'];

        $failed = (int) $summary['failed'];

        $skipped = (int) $summary['skipped'];

        $total = (int) ($summary['total'] ?? 0);



        if ($success > 0 && ($failed + $skipped) === 0) {

            return sprintf('%s از %s ردیف با موفقیت ثبت شد.', number_format($success), number_format($total));

        }



        if ($success === 0 && ($failed + $skipped) > 0) {

            $firstError = $summary['errors'][0]['messages'][0] ?? null;



            if ($firstError && !$this->rowHasMappableProductColumn($rows[0] ?? [])) {

                return $this->formatDetectedHeaders($rows[0] ?? [])

                    ? 'ستون «عنوان» یا «SKU» در فایل شناسایی نشد — ' . $this->formatDetectedHeaders($rows[0] ?? [])

                    : 'ستون «عنوان» یا «SKU» در فایل شناسایی نشد. لطفاً از قالب CSV همین صفحه استفاده کنید.';

            }



            if ($firstError) {

                return sprintf(

                    'هیچ ردیفی ثبت نشد — %s ردیف خطا داشت. نمونه خطا: %s',

                    number_format($failed + $skipped),

                    $this->humanizeImportError($firstError)

                );

            }



            return sprintf('هیچ ردیفی ثبت نشد — %s ردیف خطا یا رد شد.', number_format($failed + $skipped));

        }



        return sprintf(

            '%s ردیف موفق — %s ردیف خطا/رد شده از %s ردیف.',

            number_format($success),

            number_format($failed + $skipped),

            number_format($total)

        );

    }



    /**

     * @param  array<int, array<string, mixed>>  $rows

     */

    public function importRows(array $rows, User $user, array $options = []): array

    {

        $tenantId = $this->tenantContext->tenantId($user);

        $organizationId = $options['organization_id'] ?? $this->tenantContext->organizationId($user);

        $updateExisting = (bool) ($options['update_existing'] ?? false);

        $exchangeRunId = $options['exchange_run_id'] ?? null;



        $summary = [

            'total' => count($rows),

            'created' => 0,

            'updated' => 0,

            'skipped' => 0,

            'failed' => 0,

            'errors' => [],

        ];



        return TenantScope::forTenant($tenantId, function () use ($rows, $user, $tenantId, $organizationId, $updateExisting, $exchangeRunId, $summary) {

            if ($exchangeRunId) {

                DataExchangeRun::query()

                    ->where('id', $exchangeRunId)

                    ->update(['total_rows' => count($rows)]);

            }



            foreach ($rows as $index => $row) {

                $line = $index + 1;



                try {

                    if ($this->rowIsEmptyImportRow($row)) {

                        $summary['skipped']++;

                        continue;

                    }



                    $normalized = $this->normalizeRow($row, $user, $tenantId, $organizationId);



                    $validator = Validator::make($normalized, [

                        'title' => ['required', 'string', 'max:191'],

                        'sku' => ['required', 'string', 'max:60'],

                    ]);



                    if ($validator->fails()) {

                        $summary['failed']++;

                        $summary['errors'][] = ['line' => $line, 'messages' => $validator->errors()->all()];

                        continue;

                    }



                    $payload = $this->buildProductRecordPayload($normalized, $tenantId, $organizationId, $user->id);
                    $priceRanges = $this->extractPriceRangesFromRow($row);

                    $existing = $this->findDuplicate($tenantId, (string) $payload['sku']);



                    if ($existing && !$updateExisting) {

                        $summary['skipped']++;

                        continue;

                    }



                    if ($existing) {

                        $existing->update($this->buildProductUpdatePayload($normalized, $payload));
                        if ($priceRanges !== []) {
                            $this->pricePeriodService->syncForProduct($existing->fresh(), $priceRanges, false);
                        }

                        $summary['updated']++;

                        continue;

                    }



                    $created = Product::create($payload);
                    if ($priceRanges !== []) {
                        $this->pricePeriodService->syncForProduct($created, $priceRanges, false);
                    }

                    $summary['created']++;

                } catch (\Throwable $exception) {

                    $summary['failed']++;

                    $summary['errors'][] = ['line' => $line, 'messages' => [$exception->getMessage()]];

                }



                if ($exchangeRunId && (($index + 1) % 5 === 0 || ($index + 1) === count($rows))) {

                    $this->syncImportProgress($exchangeRunId, count($rows), $summary);

                }

            }



            if ($exchangeRunId) {

                $this->syncImportProgress($exchangeRunId, count($rows), $summary);

            }



            return $summary;

        });

    }



    private function syncImportProgress(int $exchangeRunId, int $totalRows, array $summary): void

    {

        DataExchangeRun::query()

            ->where('id', $exchangeRunId)

            ->update([

                'total_rows' => $totalRows,

                'success_rows' => (int) $summary['created'] + (int) $summary['updated'],

                'failed_rows' => (int) $summary['failed'] + (int) $summary['skipped'],

            ]);

    }



    /**

     * @return array<string, mixed>

     */

    private function normalizeRow(array $row, User $user, ?int $tenantId, ?int $defaultOrganizationId): array

    {

        $title = trim((string) ($this->pick($row, self::TITLE_ALIASES) ?? ''));

        $sku = trim((string) ($this->pick($row, self::SKU_ALIASES) ?? ''));



        if ($sku === '' && $title !== '') {

            $sku = $this->generateSku($title, $tenantId);

        }



        $baseUnit = $this->resolveUnit($this->pick($row, self::BASE_UNIT_ALIASES), $user);

        $secondaryUnit = $this->resolveUnit($this->pick($row, self::SECONDARY_UNIT_ALIASES), $user);

        $conversionFactor = $this->normalizeNumber($this->pick($row, self::CONVERSION_ALIASES)) ?: 1;

        $orderQuantityMode = $this->resolveOrderQuantityMode($this->pick($row, self::ORDER_QTY_MODE_ALIASES));

        $saleFlags = Product::saleFlagsForQuantityMode($orderQuantityMode);

        $productType = $this->resolveProductType($this->pick($row, self::PRODUCT_TYPE_ALIASES));

        $warehouseEnabled = TenantSettings::enabled('feature_warehouse_management');

        $branchEnabled = TenantSettings::enabled('feature_branch_management');



        $organizationIds = $branchEnabled

            ? $this->resolveOrganizationIds($this->pick($row, self::ORGANIZATION_ALIASES), $user, $defaultOrganizationId)

            : $this->defaultOrganizationIds($user, $defaultOrganizationId);



        $storeIds = $warehouseEnabled

            ? $this->resolveStoreIds($this->pick($row, self::STORE_ALIASES), $user)

            : [];



        return [

            'title' => $title,

            'display_name' => trim((string) ($this->pick($row, self::DISPLAY_NAME_ALIASES) ?? '')) ?: null,

            'sku' => $sku,

            'parentCategory_id' => $this->resolveCategoryId($this->pick($row, self::PARENT_CATEGORY_ALIASES), null),

            'childCategory_id' => $this->resolveCategoryId($this->pick($row, self::CHILD_CATEGORY_ALIASES), $this->pick($row, self::PARENT_CATEGORY_ALIASES)),

            'product_type' => $productType,

            'stock_tracking_mode' => $this->resolveStockTrackingMode($this->pick($row, self::STOCK_TRACKING_ALIASES)),

            'valuation_method' => $this->resolveValuationMethod($this->pick($row, self::VALUATION_ALIASES)),

            'base_unit_id' => $baseUnit?->id,

            'secondary_unit_id' => $secondaryUnit?->id,

            'pr_unit' => $baseUnit?->title,

            'pr_sub_unit' => $secondaryUnit?->title,

            'pack_items' => $conversionFactor,

            'unit_conversion_factor' => $conversionFactor,
            'pr_weight' => $this->normalizeNumber($this->pick($row, self::PR_WEIGHT_ALIASES)),
            'pr_weight_unit' => trim((string) ($this->pick($row, self::PR_WEIGHT_UNIT_ALIASES) ?? '')) ?: null,
            'pack_weight' => $this->normalizeNumber($this->pick($row, self::PACK_WEIGHT_ALIASES)),
            'pack_weight_unit' => trim((string) ($this->pick($row, self::PACK_WEIGHT_UNIT_ALIASES) ?? '')) ?: null,

            'order_quantity_mode' => $orderQuantityMode,

            'item_sale_status' => $saleFlags['item_sale_status'],

            'pack_sale_status' => $saleFlags['pack_sale_status'],

            'price' => $this->normalizeMoney($this->pick($row, self::PRICE_ALIASES)) ?? 0,

            'purchase_price' => $this->normalizeMoney($this->pick($row, self::PURCHASE_PRICE_ALIASES)),

            'cost_price' => $this->normalizeMoney($this->pick($row, self::COST_PRICE_ALIASES)),

            'representative_price' => $this->normalizeMoney($this->pick($row, self::REPRESENTATIVE_PRICE_ALIASES)),

            'wholesale_price' => $this->normalizeMoney($this->pick($row, self::WHOLESALE_PRICE_ALIASES)),

            'consumer_price' => $this->normalizeMoney($this->pick($row, self::CONSUMER_PRICE_ALIASES)),

            'fee_masraf' => $this->normalizeMoney($this->pick($row, self::CONSUMER_PRICE_ALIASES)),

            'discount' => $this->normalizeMoney($this->pick($row, self::DISCOUNT_ALIASES)) ?? 0,

            'max_discount_amount' => $this->normalizeMoney($this->pick($row, self::MAX_DISCOUNT_AMOUNT_ALIASES)),

            'tax' => $this->normalizeMoney($this->pick($row, self::TAX_ALIASES)) ?? 0,

            'brand_id' => $this->resolveBrandId($this->pick($row, self::BRAND_ALIASES)),

            'description' => trim((string) ($this->pick($row, self::DESCRIPTION_ALIASES) ?? '')) ?: null,

            'organization_id' => json_encode($organizationIds, JSON_UNESCAPED_UNICODE),

            'store_id' => json_encode($storeIds, JSON_UNESCAPED_UNICODE),

            'entity' => $this->normalizeNumber($this->pick($row, self::ENTITY_ALIASES)) ?? 0,

            'orderLimit' => $this->normalizeNumber($this->pick($row, self::ORDER_LIMIT_ALIASES)),

            'isActive' => $this->normalizeBoolean($this->pick($row, self::IS_ACTIVE_ALIASES), 1),

            'isMaterial' => $this->normalizeBoolean($this->pick($row, self::IS_MATERIAL_ALIASES), $productType === 'material' ? 1 : 0),

            'set_price' => 0,

            'user_id' => $user->id,

        ];

    }

    /**
     * @param array<string, mixed> $row
     * @return array<int, array<string, mixed>>
     */
    public function extractPriceRangesFromRow(array $row): array
    {
        $type = trim((string) ($this->pick($row, self::PRICE_RANGE_TYPE_ALIASES) ?? ""));
        $amount = $this->normalizeMoney($this->pick($row, self::PRICE_RANGE_AMOUNT_ALIASES));
        $startsAt = $this->pick($row, self::PRICE_RANGE_STARTS_AT_ALIASES);
        $endsAt = $this->pick($row, self::PRICE_RANGE_ENDS_AT_ALIASES);

        if ($type === "" || $amount === null) {
            return [];
        }

        return [[
            'price_type' => $type,
            'amount' => $amount,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'priority' => 0,
            'metadata' => ['source' => 'import'],
        ]];
    }

    /**

     * @param  array<string, mixed>  $normalized

     * @return array<string, mixed>

     */

    private function buildProductRecordPayload(array $normalized, ?int $tenantId, ?int $organizationId, int $userId): array

    {

        $payload = $normalized;

        $payload['tenant_id'] = $tenantId;

        $payload['organization_id'] = $normalized['organization_id'] ?? json_encode([(int) $organizationId], JSON_UNESCAPED_UNICODE);



        if (empty($payload['display_name'])) {

            $payload['display_name'] = $payload['title'];

        }



        return $payload;

    }



    /**

     * @param  array<string, mixed>  $normalized

     * @param  array<string, mixed>  $payload

     * @return array<string, mixed>

     */

    private function buildProductUpdatePayload(array $normalized, array $payload): array

    {

        unset($payload['user_id']);



        return $payload;

    }



    private function findDuplicate(?int $tenantId, string $sku): ?Product

    {

        return Product::withoutTenantScope()

            ->where('tenant_id', $tenantId)

            ->where('sku', $sku)

            ->first();

    }



    private function generateSku(string $title, ?int $tenantId): string

    {

        $base = preg_replace('/\s+/u', '-', trim($title)) ?: 'product';

        $base = mb_substr($base, 0, 40);



        do {

            $sku = $base . '-' . random_int(1000, 9999);

        } while (Product::withoutTenantScope()->where('tenant_id', $tenantId)->where('sku', $sku)->exists());



        return $sku;

    }



    private function resolveUnit(mixed $value, User $user): ?Unit

    {

        if ($value === null || trim((string) $value) === '') {

            return null;

        }



        $query = Unit::query()->where('isActive', 1)->forUsageScope(Unit::SCOPE_PRODUCT);



        if ((int) $user->isGod !== 1) {

            $query->forOrganizations($user);

        }



        if (is_numeric($value)) {

            return (clone $query)->where('id', (int) $value)->first()

                ?: throw new \RuntimeException(sprintf('واحد با شناسه %s یافت نشد.', $value));

        }



        $title = trim((string) $value);



        return (clone $query)->where('title', $title)->first()

            ?: throw new \RuntimeException(sprintf('واحد «%s» در فهرست واحدهای محصول ثبت نشده است.', $title));

    }



    private function resolveCategoryId(mixed $value, mixed $parentHint): ?int

    {

        if ($value === null || trim((string) $value) === '' || trim((string) $value) === '0') {

            return null;

        }



        if (is_numeric($value)) {

            return (int) $value;

        }



        $title = trim((string) $value);

        $query = Category::query()->where('isActive', 1)->where('title', $title);



        if ($parentHint !== null && trim((string) $parentHint) !== '' && !is_numeric($parentHint)) {

            $parent = Category::query()->where('isActive', 1)->where('title', trim((string) $parentHint))->first();

            if ($parent) {

                $query->where('parent_id', $parent->id);

            }

        }



        $category = $query->first();



        return $category?->id;

    }



    private function resolveBrandId(mixed $value): ?int

    {

        if ($value === null || trim((string) $value) === '' || trim((string) $value) === '0') {

            return null;

        }



        if (is_numeric($value)) {

            return (int) $value;

        }



        return Brand::query()->where('isActive', 1)->where('title', trim((string) $value))->value('id');

    }



    /**

     * @return array<int, int>

     */

    private function resolveOrganizationIds(mixed $value, User $user, ?int $defaultOrganizationId): array

    {

        if ($value === null || trim((string) $value) === '') {

            return $this->defaultOrganizationIds($user, $defaultOrganizationId);

        }



        $parts = preg_split('/[،,;|]+/u', (string) $value) ?: [];

        $ids = [];



        foreach ($parts as $part) {

            $part = trim($part);

            if ($part === '') {

                continue;

            }



            if (is_numeric($part)) {

                $ids[] = (int) $part;

                continue;

            }



            $query = Organization::query()->where('isActive', 1)->where('title', $part);

            if ((int) $user->isGod !== 1) {

                $query->forOrganizations($user, 'id');

            }



            $id = $query->value('id');

            if ($id) {

                $ids[] = (int) $id;

            }

        }



        return $ids !== [] ? array_values(array_unique($ids)) : $this->defaultOrganizationIds($user, $defaultOrganizationId);

    }



    /**

     * @return array<int, int>

     */

    private function resolveStoreIds(mixed $value, User $user): array

    {

        if ($value === null || trim((string) $value) === '') {

            return [];

        }



        $parts = preg_split('/[،,;|]+/u', (string) $value) ?: [];

        $ids = [];



        foreach ($parts as $part) {

            $part = trim($part);

            if ($part === '') {

                continue;

            }



            $query = Store::query()->where('isActive', 1);



            if ((int) $user->isGod !== 1) {

                $query->forOrganizations($user);

            }



            if (is_numeric($part)) {

                $store = (clone $query)->where('id', (int) $part)->first();

            } else {

                $store = (clone $query)->where('title', $part)->first();

            }



            if ($store) {

                $ids[] = (int) $store->id;

            }

        }



        return array_values(array_unique($ids));

    }



    /**

     * @return array<int, int>

     */

    private function defaultOrganizationIds(User $user, ?int $defaultOrganizationId): array

    {

        if ($defaultOrganizationId) {

            return [(int) $defaultOrganizationId];

        }



        $raw = json_decode((string) $user->organization_id, true) ?: $user->organization_id;

        $values = is_array($raw) ? $raw : [$raw];

        $ids = array_values(array_unique(array_filter(array_map('intval', $values))));



        if ($ids !== []) {

            return $ids;

        }



        $tenantId = $user->tenant_id ?: $user->tenants_id;

        $organizationId = Organization::query()

            ->where(function ($query) use ($tenantId) {

                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);

            })

            ->value('id');



        return $organizationId ? [(int) $organizationId] : [];

    }



    private function resolveProductType(mixed $value): string

    {

        $raw = trim((string) ($value ?? ''));



        if ($raw === '') {

            return 'goods';

        }



        $normalized = $this->normalizeColumnKey($raw);



        foreach (Product::PRODUCT_TYPE_LABELS as $key => $label) {

            if ($normalized === $key || $normalized === $this->normalizeColumnKey($label)) {

                return $key;

            }

        }



        return 'goods';

    }



    private function resolveStockTrackingMode(mixed $value): string

    {

        $raw = trim((string) ($value ?? ''));



        if ($raw === '') {

            return 'tracked';

        }



        $normalized = $this->normalizeColumnKey($raw);



        foreach (Product::STOCK_TRACKING_LABELS as $key => $label) {

            if ($normalized === $key || $normalized === $this->normalizeColumnKey($label)) {

                return $key;

            }

        }



        return 'tracked';

    }



    private function resolveValuationMethod(mixed $value): string

    {

        $raw = trim((string) ($value ?? ''));



        if ($raw === '') {

            return 'weighted_average';

        }



        $normalized = $this->normalizeColumnKey($raw);



        foreach (Product::VALUATION_METHOD_LABELS as $key => $label) {

            if ($normalized === $key || $normalized === $this->normalizeColumnKey($label)) {

                return $key;

            }

        }



        return 'weighted_average';

    }



    private function resolveOrderQuantityMode(mixed $value): string

    {

        $raw = trim((string) ($value ?? ''));



        if ($raw === '') {

            return 'main_unit';

        }



        $normalized = $this->normalizeColumnKey($raw);



        foreach (Product::ORDER_QUANTITY_MODES as $key => $label) {

            if ($normalized === $key || $normalized === $this->normalizeColumnKey($label)) {

                return $key;

            }

        }



        return 'main_unit';

    }



    private function normalizeBoolean(mixed $value, int $default): int

    {

        if ($value === null || trim((string) $value) === '') {

            return $default;

        }



        $normalized = $this->normalizeColumnKey((string) $value);



        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'on', 'بله', 'فعال', 'بلی'], true)) {

            return 1;

        }



        if (in_array($normalized, ['0', 'false', 'no', 'n', 'off', 'خیر', 'غیرفعال', 'نه'], true)) {

            return 0;

        }



        return (int) $value > 0 ? 1 : 0;

    }



    /**

     * @param  array<string, mixed>  $row

     * @param  array<int, string>  $aliases

     */

    private function pick(array $row, array $aliases): mixed

    {

        foreach ($aliases as $alias) {

            foreach ($row as $key => $value) {

                if ($this->matchesColumnAlias((string) $key, [$alias])) {

                    if ($value !== null && trim((string) $value) !== '') {

                        return $value;

                    }

                }

            }

        }



        return null;

    }



    /**

     * @param  array<int, string>  $keys

     */

    private function looksLikeNumericRowKeys(array $keys): bool

    {

        if ($keys === []) {

            return true;

        }



        foreach ($keys as $key) {

            if (!is_int($key) && !ctype_digit((string) $key)) {

                return false;

            }

        }



        return true;

    }



    /**

     * @param  array<string, mixed>  $row

     */

    private function rowHasMappableProductColumn(array $row): bool

    {

        foreach (array_keys($row) as $key) {

            $header = (string) $key;



            if ($this->matchesColumnAlias($header, self::TITLE_ALIASES)

                || $this->matchesColumnAlias($header, self::SKU_ALIASES)) {

                return true;

            }

        }



        return false;

    }



    /**

     * @param  array<string, mixed>  $row

     */

    private function rowIsEmptyImportRow(array $row): bool

    {

        $title = trim((string) ($this->pick($row, self::TITLE_ALIASES) ?? ''));

        $sku = trim((string) ($this->pick($row, self::SKU_ALIASES) ?? ''));



        return $title === '' && $sku === '';

    }



    /**

     * @param  array<int, string>  $aliases

     */

    private function matchesColumnAlias(string $key, array $aliases): bool

    {

        $normalizedKey = $this->normalizeColumnKey($key);



        foreach ($aliases as $alias) {

            $normalizedAlias = $this->normalizeColumnKey($alias);



            if ($normalizedKey === $normalizedAlias || str_contains($normalizedKey, $normalizedAlias)) {

                return true;

            }

        }



        return false;

    }



    private function normalizeColumnKey(string $value): string

    {

        $value = trim($value);

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;



        return mb_strtolower($value);

    }



    /**

     * @param  array<string, mixed>  $row

     */

    private function formatDetectedHeaders(array $row): ?string

    {

        $headers = array_values(array_filter(array_map(

            fn ($key) => trim((string) $key),

            array_keys($row)

        )));



        if ($headers === []) {

            return null;

        }



        return implode('، ', array_slice($headers, 0, 8));

    }



    private function humanizeImportError(string $message): string

    {

        if (preg_match("/Field '([^']+)' doesn't have a default value/", $message, $matches)) {

            return sprintf('فیلد «%s» در دیتابیس اجباری است اما مقدار ندارد.', $matches[1]);

        }



        if (str_contains($message, 'SQLSTATE[')) {

            return 'خطای ثبت در دیتابیس — لطفاً با پشتیبانی تماس بگیرید.';

        }



        return $message;

    }



    private function normalizeMoney(mixed $value): ?string

    {

        if ($value === null || trim((string) $value) === '') {

            return null;

        }



        $digits = preg_replace('/[^\d.-]+/', '', (string) $value);



        return $digits !== '' && $digits !== '-' ? $digits : null;

    }



    private function normalizeNumber(mixed $value): ?float

    {

        if ($value === null || trim((string) $value) === '') {

            return null;

        }



        $digits = preg_replace('/[^\d.-]+/', '', (string) $value);



        return $digits !== '' && is_numeric($digits) ? (float) $digits : null;

    }



    /**

     * @return array<int, array{header: string, required: bool, note: string}>

     */

    public function columnGuide(): array

    {

        return [

            ['header' => 'عنوان', 'required' => true, 'note' => 'الزامی — نام اصلی محصول در سیستم.'],

            ['header' => 'نام نمایشی', 'required' => false, 'note' => 'اختیاری — در صورت خالی بودن همان «عنوان» استفاده می‌شود.'],

            ['header' => 'SKU', 'required' => true, 'note' => 'الزامی — کلید یکتا محصول در پنل؛ برای به‌روزرسانی از همین ستون استفاده می‌شود.'],

            ['header' => 'دسته‌بندی', 'required' => false, 'note' => 'نام یا شناسه دسته والد — از فهرست دسته‌بندی‌های فعال.'],

            ['header' => 'زیردسته', 'required' => false, 'note' => 'نام یا شناسه زیردسته — اختیاری.'],

            ['header' => 'نوع کالا', 'required' => false, 'note' => 'مثلاً کالای قابل فروش، مواد اولیه، خدمت، بسته/کیت — پیش‌فرض: کالای قابل فروش.'],

            ['header' => 'کنترل موجودی', 'required' => false, 'note' => 'کنترل موجودی فعال یا بدون کنترل موجودی — پیش‌فرض: فعال.'],

            ['header' => 'روش ارزش‌گذاری', 'required' => false, 'note' => 'میانگین موزون یا نرخ دستی — پیش‌فرض: میانگین موزون.'],

            ['header' => 'واحد اصلی', 'required' => false, 'note' => 'نام واحد ثبت‌شده در منوی «واحدهای محصول» یا شناسه عددی — باید با واحدهای پنل هم‌خوان باشد.'],

            ['header' => 'واحد فرعی', 'required' => false, 'note' => 'نام یا شناسه واحد فرعی — اختیاری؛ باید در فهرست واحدهای محصول وجود داشته باشد.'],

            ['header' => 'ضریب تبدیل', 'required' => false, 'note' => 'تعداد واحد اصلی در هر واحد فرعی — پیش‌فرض: ۱.'],
            ['header' => 'وزن واحد اصلی', 'required' => false, 'note' => 'وزن واحد اصلی محصول برای سناریوهای باربری.'],
            ['header' => 'واحد اصلی باربری', 'required' => false, 'note' => 'واحد وزن واحد اصلی (مثلاً کیلوگرم).'],
            ['header' => 'وزن واحد فرعی', 'required' => false, 'note' => 'وزن واحد فرعی/کارتن.'],
            ['header' => 'واحد فرعی باربری', 'required' => false, 'note' => 'واحد وزن واحد فرعی.'],

            ['header' => 'نحوه فروش در سفارش', 'required' => false, 'note' => 'تک‌فروشی، فقط واحد اصلی، فقط واحد فرعی، یا هر دو واحد — پیش‌فرض: فقط واحد اصلی.'],

            ['header' => 'قیمت فروش', 'required' => false, 'note' => 'قیمت فروش پایه — عددی بدون جداکننده یا با کاما.'],

            ['header' => 'قیمت خرید', 'required' => false, 'note' => 'قیمت خرید — اختیاری.'],

            ['header' => 'قیمت تمام‌شده', 'required' => false, 'note' => 'بهای تمام‌شده — اختیاری.'],

            ['header' => 'قیمت نماینده', 'required' => false, 'note' => 'قیمت فروش نماینده — در پنل‌های دارای فروش نمایندگی.'],

            ['header' => 'قیمت عمده', 'required' => false, 'note' => 'قیمت عمده‌فروشی — اختیاری.'],

            ['header' => 'قیمت مصرف‌کننده', 'required' => false, 'note' => 'قیمت مصرف‌کننده / fee — اختیاری.'],

            ['header' => 'حداکثر درصد تخفیف', 'required' => false, 'note' => 'سقف درصد تخفیف مجاز در سفارش — پیش‌فرض: ۰.'],

            ['header' => 'حداکثر مبلغ تخفیف', 'required' => false, 'note' => 'سقف مبلغ تخفیف به ریال/تومان — اختیاری.'],

            ['header' => 'مالیات', 'required' => false, 'note' => 'درصد مالیات — پیش‌فرض: ۰.'],
            ['header' => 'بازه قیمت نوع', 'required' => false, 'note' => 'نوع بازه: sale, buy, cost, agent, wholesale, consumer.'],
            ['header' => 'بازه قیمت مبلغ', 'required' => false, 'note' => 'مبلغ قیمت برای بازه زمانی.'],
            ['header' => 'بازه قیمت از تاریخ', 'required' => false, 'note' => 'تاریخ شروع بازه (جلالی یا میلادی).'],
            ['header' => 'بازه قیمت تا تاریخ', 'required' => false, 'note' => 'تاریخ پایان بازه (جلالی یا میلادی).'],

            ['header' => 'برند', 'required' => false, 'note' => 'نام یا شناسه برند — اختیاری.'],

            ['header' => 'توضیحات', 'required' => false, 'note' => 'شرح آزاد محصول.'],

            ['header' => 'واحد پخش', 'required' => false, 'note' => 'نام یا شناسه واحد پخش — در پنل‌های چندشعبه‌ای؛ در غیر این صورت از پیش‌فرض پنل استفاده می‌شود.'],

            ['header' => 'انبار', 'required' => false, 'note' => 'فقط برای پنل‌های دارای ماژول انبار — نام یا شناسه انبار (چند مقدار با کاما). در پنل‌های بدون انبار خالی بگذارید.'],

            ['header' => 'موجودی اولیه', 'required' => false, 'note' => 'تعداد موجودی اولیه — فقط در پنل‌های دارای انبار معنا دارد؛ در غیر این صورت نادیده گرفته می‌شود.'],

            ['header' => 'محدودیت سفارش', 'required' => false, 'note' => 'حداکثر تعداد قابل سفارش — اختیاری.'],

            ['header' => 'وضعیت فعال', 'required' => false, 'note' => '۱/بله/فعال یا ۰/خیر — پیش‌فرض: فعال.'],

            ['header' => 'مواد اولیه', 'required' => false, 'note' => '۱ برای مواد اولیه — یا از ستون «نوع کالا» استفاده کنید.'],

        ];

    }



    public function templateHeaders(): array

    {

        return array_column($this->columnGuide(), 'header');

    }



    /**

     * @return array<int, array<int, string>>

     */

    public function templateSampleRows(): array

    {

        return [

            [

                'شیر پاستوریزه یک لیتری',

                'شیر ۱L',

                'PRD-1001',

                'لبنیات',

                '',

                'کالای قابل فروش',

                'کنترل موجودی فعال',

                'میانگین موزون',

                'عدد',

                'کارتن',

                '12',

                '1',

                'کیلوگرم',

                '12',

                'کیلوگرم',

                'فقط واحد اصلی',

                '85000',

                '70000',

                '72000',

                '80000',

                '78000',

                '85000',

                '10',

                '5000',

                '9',

                'sale',

                '85000',

                '1405/01/01',

                '1405/12/29',

                'برند نمونه',

                'محصول نمونه import',

                '',

                '',

                '120',

                '',

                'فعال',

                'خیر',

            ],

            [

                'آرد گندم ۱۰ کیلویی',

                'آرد 10kg',

                'PRD-1002',

                'خشکبار',

                '',

                'کالای قابل فروش',

                'کنترل موجودی فعال',

                'میانگین موزون',

                'کیلوگرم',

                'کیسه',

                '10',

                '10',

                'کیلوگرم',

                '100',

                'کیلوگرم',

                'هر دو واحد',

                '450000',

                '380000',

                '',

                '',

                '420000',

                '450000',

                '5',

                '',

                '0',

                'buy',

                '380000',

                '1405/01/01',

                '',

                '',

                '',

                '',

                'انبار مرکزی',

                '50',

                '100',

                'بله',

                'خیر',

            ],

        ];

    }



    /**

     * @return array<int, array{message: string, count: int, lines: array<int, int>}>

     */

    public function summarizeRowErrors(array $summary, int $limit = 8): array

    {

        $grouped = [];



        foreach ($summary['errors'] ?? [] as $error) {

            $message = trim((string) ($error['messages'][0] ?? 'خطای نامشخص'));

            $line = (int) ($error['line'] ?? 0);



            if (!isset($grouped[$message])) {

                $grouped[$message] = [

                    'message' => $message,

                    'count' => 0,

                    'lines' => [],

                ];

            }



            $grouped[$message]['count']++;



            if ($line > 0 && count($grouped[$message]['lines']) < 5) {

                $grouped[$message]['lines'][] = $line;

            }

        }



        return array_slice(array_values($grouped), 0, $limit);

    }

}


