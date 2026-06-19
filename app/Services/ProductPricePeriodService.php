<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPricePeriod;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductPricePeriodService
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function syncForProduct(Product $product, array $rows, bool $replaceExisting = true): void
    {
        $normalizedRows = $this->normalizeRows($rows);

        if ($normalizedRows === []) {
            return;
        }

        if ($replaceExisting) {
            $product->pricePeriods()->delete();
        }

        foreach ($normalizedRows as $row) {
            $product->pricePeriods()->create([
                "tenant_id" => $product->tenant_id,
                "price_type" => $row["price_type"],
                "amount" => $row["amount"],
                "starts_at" => $row["starts_at"],
                "ends_at" => $row["ends_at"],
                "starts_at_fa" => $row["starts_at_fa"],
                "ends_at_fa" => $row["ends_at_fa"],
                "priority" => $row["priority"] ?? 0,
                "status" => true,
                "metadata" => $row["metadata"] ?? null,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public function normalizeRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            $priceType = $this->normalizePriceType((string) ($row["price_type"] ?? ""));
            $amountRaw = $row["amount"] ?? null;

            if ($priceType === "" && ($amountRaw === null || trim((string) $amountRaw) === "")) {
                continue;
            }

            if ($priceType === "" || !isset(ProductPricePeriod::PRICE_TYPES[$priceType])) {
                throw ValidationException::withMessages([
                    "price_ranges.$index.price_type" => "نوع قیمت بازه معتبر نیست.",
                ]);
            }

            $amount = $this->normalizeMoney($amountRaw);
            if ($amount === null || $amount < 0) {
                throw ValidationException::withMessages([
                    "price_ranges.$index.amount" => "مقدار قیمت بازه معتبر نیست.",
                ]);
            }

            [$startsAt, $startsAtFa] = $this->parseFlexibleDate($row["starts_at"] ?? null);
            [$endsAt, $endsAtFa] = $this->parseFlexibleDate($row["ends_at"] ?? null);

            if ($startsAt && $endsAt && $startsAt->gt($endsAt)) {
                throw ValidationException::withMessages([
                    "price_ranges.$index.starts_at" => "تاریخ شروع بازه نمی تواند بعد از تاریخ پایان باشد.",
                ]);
            }

            $normalized[] = [
                "price_type" => $priceType,
                "amount" => $amount,
                "starts_at" => $startsAt?->toDateString(),
                "ends_at" => $endsAt?->toDateString(),
                "starts_at_fa" => $startsAtFa,
                "ends_at_fa" => $endsAtFa,
                "priority" => (int) ($row["priority"] ?? 0),
                "metadata" => $row["metadata"] ?? null,
            ];
        }

        $this->assertNoOverlaps($normalized);

        return $normalized;
    }

    public function resolveActivePrice(Product $product, string $priceType, ?Carbon $at = null): ?ProductPricePeriod
    {
        $at = $at ?: now();
        $date = $at->toDateString();

        return $product->pricePeriods()
            ->where("price_type", $priceType)
            ->where("status", true)
            ->where(function ($query) use ($date) {
                $query->whereNull("starts_at")->orWhere("starts_at", "<=", $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull("ends_at")->orWhere("ends_at", ">=", $date);
            })
            ->orderByDesc("priority")
            ->orderByDesc("starts_at")
            ->first();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function assertNoOverlaps(array $rows): void
    {
        $grouped = collect($rows)->groupBy("price_type");

        $grouped->each(function (Collection $items, string $priceType) {
            $sorted = $items->sortBy(function (array $item) {
                return $item["starts_at"] ?: "0001-01-01";
            })->values();

            $lastEnd = null;
            foreach ($sorted as $idx => $item) {
                if ($idx === 0) {
                    $lastEnd = $item["ends_at"];
                    continue;
                }

                $start = $item["starts_at"];
                if ($lastEnd === null || $start === null) {
                    throw ValidationException::withMessages([
                        "price_ranges" => "برای نوع {$priceType} بازه های بدون پایان یا بدون شروع با سایر بازه ها همپوشانی دارند.",
                    ]);
                }

                if ($start <= $lastEnd) {
                    throw ValidationException::withMessages([
                        "price_ranges" => "برای نوع {$priceType} بازه های قیمتی همپوشانی دارند.",
                    ]);
                }

                $lastEnd = $item["ends_at"];
            }
        });
    }

    /**
     * @return array{0: ?Carbon, 1: ?string}
     */
    private function parseFlexibleDate(mixed $value): array
    {
        $value = trim((string) ($value ?? ""));
        if ($value === "") {
            return [null, null];
        }

        if (preg_match("/^\d{4}\/\d{1,2}\/\d{1,2}$/", $value) === 1) {
            [$jy, $jm, $jd] = array_map("intval", explode("/", $value));
            $gregorian = Verta::jalaliToGregorian($jy, $jm, $jd);
            $date = Carbon::createFromDate((int) $gregorian[0], (int) $gregorian[1], (int) $gregorian[2])->startOfDay();

            return [$date, sprintf("%04d/%02d/%02d", $jy, $jm, $jd)];
        }

        try {
            $date = Carbon::parse($value)->startOfDay();
            $fa = Verta::instance($date)->format("Y/m/d");

            return [$date, $fa];
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                "price_ranges" => "فرمت تاریخ بازه معتبر نیست: {$value}",
            ]);
        }
    }

    private function normalizeMoney(mixed $value): ?float
    {
        $digits = preg_replace("/[^\d.-]+/", "", trim((string) ($value ?? "")));
        if ($digits === "" || $digits === "-") {
            return null;
        }

        return (float) $digits;
    }

    private function normalizePriceType(string $value): string
    {
        $raw = trim(mb_strtolower($value));
        if ($raw === "") {
            return "";
        }

        if (isset(ProductPricePeriod::PRICE_TYPES[$raw])) {
            return $raw;
        }

        foreach (ProductPricePeriod::PRICE_TYPES as $key => $label) {
            if (trim(mb_strtolower($label)) === $raw) {
                return $key;
            }
        }

        return $raw;
    }
}

