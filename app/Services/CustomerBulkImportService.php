<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\DataExchangeRun;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CustomerBulkImportService
{
    public const TEMPLATE_FILENAME = 'customers-import-template-v2.csv';

    private const NAME_ALIASES = ['name', 'نام', 'نام مشتری', 'مشتری', 'نام و نام خانوادگی', 'خریدار', 'نام خریدار'];
    private const MOBILE_ALIASES = ['mobile', 'موبایل', 'شماره موبایل', 'تلفن همراه', 'همراه', 'شماره تماس', 'موبایل مشتری'];
    private const NATIONAL_ID_ALIASES = ['national_id', 'کد ملی', 'کدملی', 'کد_ملی'];
    private const TABLO_ALIASES = ['tablo', 'تابلو'];
    private const AMOUNT_ALIASES = ['amount', 'مبلغ', 'مبلغ کل'];
    private const SUBSCRIPTION_TERM_ALIASES = ['subscription_term', 'مدت اشتراک', 'مدت', 'اشتراک'];
    private const PURCHASE_DATE_ALIASES = ['purchase_date', 'تاریخ خرید', 'تاریخ'];
    private const SUBSCRIPTION_END_ALIASES = ['subscription_end', 'پایان اشتراک', 'تاریخ پایان'];
    private const SETTLEMENT_STATUS_ALIASES = ['settlement_status', 'وضعیت تسویه', 'وضعیت'];
    private const PREPAYMENT_ALIASES = ['prepayment', 'پیش‌پرداخت', 'پیش پرداخت'];
    private const FULL_PAYMENT_ALIASES = ['full_payment', 'تکمیل وجه', 'تکمیل'];
    private const DEBT_ALIASES = ['debt', 'بدهی', 'مانده بدهی'];
    private const NOTES_ALIASES = ['notes', 'description', 'توضیحات', 'شرح'];
    private const REGISTRAR_MOBILE_ALIASES = ['registrar_mobile', 'موبایل ثبت‌کننده', 'موبایل ثبت کننده', 'ثبت‌کننده'];

    public function __construct(private TenantContextService $tenantContext) {}

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

        if (!$this->rowHasMappableCustomerColumn($firstRow)) {
            $headers = $this->formatDetectedHeaders($firstRow);

            throw new \RuntimeException(
                'ستون «نام» یا «موبایل» در سرستون فایل یافت نشد.'
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

            if ($firstError === 'فیلد نام الزامی است.' && !$this->rowHasMappableCustomerColumn($rows[0] ?? [])) {
                return $this->formatDetectedHeaders($rows[0] ?? [])
                    ? 'ستون «نام» یا «موبایل» در فایل شناسایی نشد — ' . $this->formatDetectedHeaders($rows[0] ?? [])
                    : 'ستون «نام» یا «موبایل» در فایل شناسایی نشد. لطفاً از قالب CSV همین صفحه استفاده کنید.';
            }

            if ($firstError) {
                $friendlyError = $this->humanizeImportError($firstError);

                return sprintf(
                    'هیچ ردیفی ثبت نشد — %s ردیف خطا داشت. نمونه خطا: %s',
                    number_format($failed + $skipped),
                    $friendlyError
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
        $defaultStatus = (int) ($options['default_status'] ?? 1);
        $exchangeRunId = $options['exchange_run_id'] ?? null;

        $summary = [
            'total' => count($rows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        return TenantScope::forTenant($tenantId, function () use ($rows, $user, $tenantId, $organizationId, $updateExisting, $defaultStatus, $exchangeRunId, $summary) {
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

                    $normalized = $this->normalizeRow($row);

                    $validator = Validator::make($normalized, [
                        'name' => ['required', 'string', 'max:191'],
                        'mobile' => ['required', 'string', 'max:30'],
                        'phone' => ['nullable', 'string', 'max:30'],
                        'customer_code' => ['nullable', 'string', 'max:60'],
                        'national_id' => ['nullable', 'string', 'max:20'],
                        'address' => ['nullable', 'string', 'max:500'],
                    ]);

                    if ($validator->fails()) {
                        $summary['failed']++;
                        $summary['errors'][] = ['line' => $line, 'messages' => $validator->errors()->all()];
                        continue;
                    }

                    $payload = $this->buildCustomerRecordPayload(
                        $normalized,
                        $tenantId,
                        $organizationId,
                        $defaultStatus,
                        $user->id
                    );

                    $existing = $this->findDuplicate($tenantId, $payload);

                    if ($existing && !$updateExisting) {
                        $summary['skipped']++;
                        continue;
                    }

                    if ($existing) {
                        $existing->update($this->buildCustomerUpdatePayload($normalized, $payload, $defaultStatus));
                        $summary['updated']++;
                        continue;
                    }

                    Customers::create($payload);
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

    private function normalizeRow(array $row): array
    {
        $mapped = [
            'name' => trim((string) ($this->pick($row, self::NAME_ALIASES) ?? '')),
            'mobile' => $this->normalizePhone($this->pick($row, self::MOBILE_ALIASES)),
            'phone' => $this->normalizePhone($this->pick($row, ['phone', 'تلفن', 'تلفن ثابت'])),
            'customer_code' => trim((string) ($this->pick($row, ['customer_code', 'code', 'کد', 'کد مشتری']) ?? '')) ?: null,
            'national_id' => trim((string) ($this->pick($row, self::NATIONAL_ID_ALIASES) ?? '')) ?: null,
            'address' => trim((string) ($this->pick($row, ['address', 'آدرس']) ?? '')) ?: null,
            'tablo' => trim((string) ($this->pick($row, self::TABLO_ALIASES) ?? '')) ?: null,
            'area' => ($value = $this->pick($row, ['area', 'area_id', 'منطقه'])) !== null ? (int) $value : null,
            'region_id' => ($value = $this->pick($row, ['region_id', 'region', 'ناحیه'])) !== null ? (int) $value : null,
            'status' => ($value = $this->pick($row, ['status', 'isActive'])) !== null ? (int) $value : null,
            'amount' => $this->normalizeMoney($this->pick($row, self::AMOUNT_ALIASES)),
            'subscription_term' => trim((string) ($this->pick($row, self::SUBSCRIPTION_TERM_ALIASES) ?? '')) ?: null,
            'purchase_date' => trim((string) ($this->pick($row, self::PURCHASE_DATE_ALIASES) ?? '')) ?: null,
            'subscription_end' => trim((string) ($this->pick($row, self::SUBSCRIPTION_END_ALIASES) ?? '')) ?: null,
            'settlement_status' => trim((string) ($this->pick($row, self::SETTLEMENT_STATUS_ALIASES) ?? '')) ?: null,
            'prepayment' => $this->normalizeMoney($this->pick($row, self::PREPAYMENT_ALIASES)),
            'full_payment' => $this->normalizeMoney($this->pick($row, self::FULL_PAYMENT_ALIASES)),
            'debt' => $this->normalizeMoney($this->pick($row, self::DEBT_ALIASES)),
            'notes' => trim((string) ($this->pick($row, self::NOTES_ALIASES) ?? '')) ?: null,
            'registrar_mobile' => $this->normalizePhone($this->pick($row, self::REGISTRAR_MOBILE_ALIASES)),
        ];

        return array_filter($mapped, function ($value, string $key) {
            if (in_array($key, ['status', 'area', 'region_id'], true)) {
                return $value !== null;
            }

            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $aliases
     */
    private function pick(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row)) {
                $value = $row[$alias];

                if ($value !== null && trim((string) $value) !== '') {
                    return $value;
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
    private function rowHasMappableCustomerColumn(array $row): bool
    {
        foreach (array_keys($row) as $key) {
            $header = (string) $key;

            if ($this->matchesColumnAlias($header, self::NAME_ALIASES)
                || $this->matchesColumnAlias($header, self::MOBILE_ALIASES)) {
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
        $name = trim((string) ($this->pick($row, self::NAME_ALIASES) ?? ''));
        $mobile = trim((string) ($this->normalizePhone($this->pick($row, self::MOBILE_ALIASES)) ?? ''));

        return $name === '' && $mobile === '';
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

    private function findDuplicate(?int $tenantId, array $payload): ?Customers
    {
        $query = Customers::withoutTenantScope()->where('tenant_id', $tenantId);

        if (!empty($payload['customer_code'])) {
            return (clone $query)->where('customer_code', $payload['customer_code'])->first();
        }

        if (!empty($payload['mobile'])) {
            return (clone $query)->where('mobile', $payload['mobile'])->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @return array<string, mixed>
     */
    private function buildCustomerRecordPayload(
        array $normalized,
        ?int $tenantId,
        ?int $organizationId,
        int $defaultStatus,
        int $userId
    ): array {
        $mobile = (string) ($normalized['mobile'] ?? '');

        return [
            'name' => (string) $normalized['name'],
            'mobile' => $mobile,
            'phone' => (string) ($normalized['phone'] ?? $mobile),
            'national_id' => (string) ($normalized['national_id'] ?? ''),
            'customer_code' => (string) ($normalized['customer_code'] ?? $this->generateCustomerCode()),
            'address' => (string) ($normalized['address'] ?? ''),
            'tablo' => $normalized['tablo'] ?? null,
            'region_id' => (int) ($normalized['region_id'] ?? 0),
            'area' => (int) ($normalized['area'] ?? 0),
            'status' => (int) ($normalized['status'] ?? $defaultStatus),
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'created_by' => $userId,
        ];
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildCustomerUpdatePayload(array $normalized, array $payload, int $defaultStatus): array
    {
        $updates = Arr::only($normalized, [
            'name', 'mobile', 'phone', 'customer_code', 'national_id', 'address', 'tablo', 'status', 'area', 'region_id',
        ]);

        if (!array_key_exists('phone', $updates) && !empty($payload['mobile'])) {
            $updates['phone'] = $payload['mobile'];
        }

        if (!array_key_exists('status', $updates)) {
            $updates['status'] = $defaultStatus;
        }

        return $updates;
    }

    private function generateCustomerCode(): string
    {
        do {
            $code = (string) random_int(1000000000, 9999999999);
        } while (Customers::withoutTenantScope()->where('customer_code', $code)->exists());

        return $code;
    }

    private function humanizeImportError(string $message): string
    {
        if (str_contains($message, "Field 'national_id' doesn't have a default value")) {
            return 'فیلد کد ملی در دیتابیس اجباری است اما در import مقداردهی نشده بود.';
        }

        if (str_contains($message, "Field 'phone' doesn't have a default value")) {
            return 'فیلد تلفن در دیتابیس اجباری است اما در import مقداردهی نشده بود.';
        }

        if (str_contains($message, "Field 'address' doesn't have a default value")) {
            return 'فیلد آدرس در دیتابیس اجباری است اما در import مقداردهی نشده بود.';
        }

        if (preg_match("/Field '([^']+)' doesn't have a default value/", $message, $matches)) {
            return sprintf('فیلد «%s» در دیتابیس اجباری است اما مقدار ندارد.', $matches[1]);
        }

        if (str_contains($message, 'SQLSTATE[')) {
            return 'خطای ثبت در دیتابیس — لطفاً با پشتیبانی تماس بگیرید.';
        }

        return $message;
    }

    private function normalizePhone(mixed $value): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $value);

        return $phone !== '' ? $phone : null;
    }

    private function normalizeMoney(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $digits = preg_replace('/[^\d.-]+/', '', (string) $value);

        return $digits !== '' && $digits !== '-' ? $digits : null;
    }

    /**
     * @return array<int, array{header: string, required: bool, note: string}>
     */
    public function columnGuide(): array
    {
        return [
            ['header' => 'نام', 'required' => true, 'note' => 'الزامی — نام مشتری باید در هر ردیف پر شود.'],
            ['header' => 'موبایل', 'required' => true, 'note' => 'الزامی — کلید یکتا مشتری در پنل؛ بدون موبایل ردیف پذیرفته نمی‌شود.'],
            ['header' => 'کد ملی', 'required' => false, 'note' => 'اختیاری — برای تکمیل پرونده مشتری.'],
            ['header' => 'تابلو', 'required' => false, 'note' => 'اختیاری — عنوان/کد تابلو مشتری.'],
            ['header' => 'مبلغ', 'required' => false, 'note' => 'برای ثبت واریزی/خرید لازم است؛ در فاز فعلی فقط مشتری ثبت می‌شود.'],
            ['header' => 'مدت اشتراک', 'required' => false, 'note' => 'برای «تکمیل وجه» و صدور فاکتور — تعداد ماه.'],
            ['header' => 'تاریخ خرید', 'required' => false, 'note' => 'تاریخ شروع تراکنش (مثلاً 1405/01/17).'],
            ['header' => 'پایان اشتراک', 'required' => false, 'note' => 'برای خرید اشتراکی — می‌تواند خالی بماند.'],
            ['header' => 'وضعیت تسویه', 'required' => false, 'note' => 'مثلاً بدهی، تسویه — برای پردازش مالی آینده.'],
            ['header' => 'پیش‌پرداخت', 'required' => false, 'note' => 'مبلغ واریزی جزئی — یکی از پیش‌پرداخت/تکمیل وجه/بدهی را پر کنید.'],
            ['header' => 'تکمیل وجه', 'required' => false, 'note' => 'مبلغ تکمیل خرید — برای صدور فاکتور کامل.'],
            ['header' => 'بدهی', 'required' => false, 'note' => 'مانده بدهی — اختیاری.'],
            ['header' => 'توضیحات', 'required' => false, 'note' => 'یادداشت آزاد برای ردیف.'],
            ['header' => 'موبایل ثبت‌کننده', 'required' => false, 'note' => 'موبایل کاربر ثبت‌کننده — اگر خالی باشد از «ثبت‌کننده پیش‌فرض» فرم استفاده می‌شود.'],
        ];
    }

    public function templateHeaders(): array
    {
        return [
            'نام',
            'موبایل',
            'کد ملی',
            'تابلو',
            'مبلغ',
            'مدت اشتراک',
            'تاریخ خرید',
            'پایان اشتراک',
            'وضعیت تسویه',
            'پیش‌پرداخت',
            'تکمیل وجه',
            'بدهی',
            'توضیحات',
            'موبایل ثبت‌کننده',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function templateSampleRows(): array
    {
        return [
            ['آقای علی رضایی', '0912***3456', '', '', '1000000', '', '1405/01/17', '', 'بدهی', '1000000', '', '', 'پیش پرداخت', ''],
            ['آقای علی رضایی', '0912***3456', '', '', '800000', '', '1405/01/17', '', 'بدهی', '800000', '', '', 'پیش پرداخت', ''],
            ['آقای علی رضایی', '0912***3456', '', '', '600000', '2', '1405/01/17', '1405/03/17', 'تسویه', '', '600000', '', 'تکمیل وجه', ''],
            ['علی نمونه', '09121234567', '0012345678', 'تابلو A', '5000000', '12', '1403/01/15', '1404/01/15', 'تسویه', '1000000', '4000000', '0', 'مشتری نمونه', '09120000000'],
        ];
    }

    public function templateSampleRow(): array
    {
        return $this->templateSampleRows()[0];
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
