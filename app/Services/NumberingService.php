<?php

namespace App\Services;

use App\Models\DocumentNumberingSequence;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NumberingService
{
    public function nextVoucherNumber(?int $tenantId = null, ?string $date = null, string $prefix = 'ACC'): string
    {
        $year = $this->jalaliYear($date);

        if (Schema::hasTable('document_numbering_sequences')) {
            return $this->nextFromSequence($tenantId, null, $this->documentType($prefix), $prefix, $year);
        }

        return $this->nextFromVouchers($tenantId, $year, $prefix);
    }

    public function nextDocumentNumber(string $documentType, string $prefix, ?int $tenantId = null, ?int $organizationId = null, ?string $date = null): string
    {
        $year = $this->jalaliYear($date);

        if (Schema::hasTable('document_numbering_sequences')) {
            return $this->nextFromSequence($tenantId, $organizationId, $documentType, $prefix, $year);
        }

        return $this->nextFromVouchers($tenantId, $year, $prefix);
    }

    private function nextFromSequence(?int $tenantId, ?int $organizationId, string $documentType, string $prefix, string $year): string
    {
        return DB::transaction(function () use ($tenantId, $organizationId, $documentType, $prefix, $year) {
            $sequence = DocumentNumberingSequence::where('tenant_id', $tenantId)
                ->where('organization_id', $organizationId)
                ->where('document_type', $documentType)
                ->where('prefix', $prefix)
                ->where(function ($query) use ($year) {
                    $query->where('year', $year)->orWhereNull('year');
                })
                ->where('isActive', 1)
                ->orderByRaw('year is null')
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = DocumentNumberingSequence::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'document_type' => $documentType,
                    'prefix' => $prefix,
                    'year' => $year,
                    'next_number' => 1,
                    'padding' => 6,
                    'separator' => '-',
                    'reset_yearly' => true,
                    'isActive' => true,
                ]);
            }

            $number = $sequence->next_number;
            $sequence->increment('next_number');

            return implode($sequence->separator, [
                $sequence->prefix,
                $year,
                str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT),
            ]);
        });
    }

    private function nextFromVouchers(?int $tenantId, string $year, string $prefix): string
    {
        $base = $prefix . '-' . $year . '-';

        $query = Voucher::query()->where('voucher_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('voucher_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function documentType(string $prefix): string
    {
        return match ($prefix) {
            'SAL' => 'sales_voucher',
            'INV' => 'inventory_receipt',
            'PUR' => 'purchase_order',
            'PRT' => 'purchase_return',
            'PUP' => 'purchase_payment',
            'TRF' => 'treasury_transfer',
            'CHK' => 'cheque_operation',
            default => 'accounting_voucher',
        };
    }

    private function jalaliYear(?string $date): string
    {
        try {
            return verta($date ?: now())->format('Y');
        } catch (\Throwable $exception) {
            return now()->format('Y');
        }
    }
}
