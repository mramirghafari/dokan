<?php

namespace App\Services;

use App\Models\City;
use App\Models\Pishfactor;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PishFactorListService
{
    public const LIST_PENDING = 'pending';
    public const LIST_ACTIVE = 'active';
    public const LIST_DECLINED = 'declined';
    public const LIST_COMPLETED = 'completed';
    public const LIST_ALL = 'all';
    public const LIST_CUSTOMER = 'customer';

    public function resolvePageContext(User $user): array
    {
        $isVisitor = false;
        $isManager = false;
        $isLeader = false;

        if ((int) $user->isGod !== 1) {
            foreach ($user->roles as $role) {
                $isVisitor = $role->title === 'visitor';
                $isManager = $role->title === 'expert';
                $isLeader = $role->title === 'leader';
            }
        }

        if ((int) $user->isGod === 1) {
            $Cities = City::all();
        } else {
            $Cities = City::forOrganizations($user)->get();
        }

        return compact('isVisitor', 'isManager', 'isLeader', 'Cities');
    }

    public function filterValues(Request $request): array
    {
        return [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'delivery_from_date' => $request->input('delivery_from_date'),
            'delivery_to_date' => $request->input('delivery_to_date'),
            'leader_id' => $request->input('leader_id'),
            'visitor_id' => $request->input('visitor_id'),
            'customer_id' => $request->input('customer_id'),
        ];
    }

    public function scopedQuery(User $user, string $listKey, array $roles, ?int $customerId = null): Builder
    {
        $query = Pishfactor::query()->select('pishfactors.*');
        $this->applyListKeyScope($query, $listKey, $customerId);
        $this->applyRoleScope($query, $user, $roles, $listKey);

        return $query;
    }

    public function filteredQuery(User $user, string $listKey, Request $request, array $roles): Builder
    {
        $customerId = $listKey === self::LIST_CUSTOMER ? (int) $request->input('customer_id') : null;

        return $this->applyFilters(
            $this->scopedQuery($user, $listKey, $roles, $customerId),
            $request
        );
    }

    public function count(User $user, string $listKey, Request $request, array $roles): int
    {
        return (int) $this->filteredQuery($user, $listKey, $request, $roles)->count('pishfactors.id');
    }

    public function sortableColumnMap(bool $showStoreColumn, bool $showVisitorColumn): array
    {
        $map = [
            1 => 'pishfactors.id',
            2 => 'pishfactors.created_at',
            3 => 'pishfactors.invoiceID',
        ];

        $deliveryIdx = 4 + ($showStoreColumn ? 1 : 0) + 4;
        $map[$deliveryIdx] = 'pishfactors.recive_date_en';
        $map[$deliveryIdx + 1] = 'pishfactors.fullPrice';
        $statusIdx = $deliveryIdx + 2 + ($showVisitorColumn ? 1 : 0) + 1;
        $map[$statusIdx] = 'pishfactors.status';

        return $map;
    }

    public function datatableColumnCount(bool $showStoreColumn, bool $showVisitorColumn): int
    {
        $count = 13;

        if ($showStoreColumn) {
            $count++;
        }

        if ($showVisitorColumn) {
            $count++;
        }

        return $count;
    }

    public function datatable(
        User $user,
        string $listKey,
        Request $request,
        array $roles,
        int $start,
        int $length,
        int $orderColumn,
        string $orderDirection,
        bool $showStoreColumn,
        bool $showVisitorColumn,
        bool $canDelete
    ): array {
        $scopedQuery = $this->scopedQuery(
            $user,
            $listKey,
            $roles,
            $listKey === self::LIST_CUSTOMER ? (int) $request->input('customer_id') : null
        );
        $recordsTotal = (clone $scopedQuery)->count('pishfactors.id');

        $filteredQuery = $this->applyFilters(clone $scopedQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count('pishfactors.id');

        $sortableColumns = $this->sortableColumnMap($showStoreColumn, $showVisitorColumn);

        $factors = (clone $filteredQuery)
            ->with([
                'customer:id,name,tablo',
                'visitor:id,name',
                'leader:id,name',
                'agencyUser:id,name',
                'organization:id,title,sub_unit,unit_order',
            ])
            ->withCount('items as items_count')
            ->withSum('items as items_pack_sum', 'pack')
            ->withSum('items as items_tedad_sum', 'tedad')
            ->when(isset($sortableColumns[$orderColumn]), function (Builder $query) use ($sortableColumns, $orderColumn, $orderDirection) {
                $query->orderBy($sortableColumns[$orderColumn], $orderDirection);
            }, function (Builder $query) {
                $query->orderByDesc('pishfactors.id');
            })
            ->skip($start)
            ->take($length)
            ->get();

        $csrf = csrf_token();
        $data = $factors->values()->map(function (Pishfactor $factor, int $index) use (
            $start,
            $showStoreColumn,
            $showVisitorColumn,
            $canDelete,
            $csrf
        ) {
            return $this->formatDatatableRow($factor, $start + $index + 1, $showStoreColumn, $showVisitorColumn, $canDelete, $csrf);
        });

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    private function applyListKeyScope(Builder $query, string $listKey, ?int $customerId = null): void
    {
        match ($listKey) {
            self::LIST_PENDING => $query->whereIn('pishfactors.status', [0, 5]),
            self::LIST_ACTIVE => $query->where('pishfactors.status', 1)->where('pishfactors.step', '<', 2),
            self::LIST_DECLINED => $query->where('pishfactors.status', 3),
            self::LIST_COMPLETED => $query->where('pishfactors.status', 4),
            self::LIST_ALL => $query->whereIn('pishfactors.status', [1, 4, 5]),
            self::LIST_CUSTOMER => $query->where('pishfactors.customer_id', $customerId ?: 0),
            default => $query->whereIn('pishfactors.status', [0, 5]),
        };
    }

    private function applyRoleScope(Builder $query, User $user, array $roles, string $listKey): Builder
    {
        if ($listKey === self::LIST_CUSTOMER) {
            if ((int) $user->isGod === 1) {
                return $query;
            }

            return $query->forOrganizations($user);
        }

        if ((int) $user->isGod === 1) {
            return $query;
        }

        if ((int) $user->isAdmin === 1) {
            return $query->forOrganizations($user);
        }

        if (!empty($roles['isVisitor'])) {
            return $query->forOrganizations($user)->where('pishfactors.visitor_id', $user->id);
        }

        if (!empty($roles['isManager'])) {
            return $query->forOrganizations($user);
        }

        return $query->forOrganizations($user)->where('pishfactors.sarparast_id', $user->id);
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $range = $this->jalaliRange($request->input('from_date'), $request->input('to_date'));
            if ($range) {
                $query->whereBetween('pishfactors.created_at', $range);
            }
        }

        if ($request->filled('delivery_from_date') && $request->filled('delivery_to_date')) {
            $range = $this->jalaliRange($request->input('delivery_from_date'), $request->input('delivery_to_date'));
            if ($range) {
                $query->whereBetween('pishfactors.recive_date_en', $range);
            }
        }

        if ($request->filled('leader_id')) {
            $query->where('pishfactors.sarparast_id', (int) $request->leader_id);
        }

        if ($request->filled('visitor_id')) {
            $query->where('pishfactors.visitor_id', (int) $request->visitor_id);
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('pishfactors.invoiceID', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('tablo', 'like', '%' . $search . '%')
                            ->orWhere('customer_code', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    private function jalaliRange(?string $from, ?string $to): ?array
    {
        if (!$from || !$to) {
            return null;
        }

        $fromParts = $this->jalaliToMiladiParts($from);
        $toParts = $this->jalaliToMiladiParts($to);

        if (!$fromParts || !$toParts) {
            return null;
        }

        return [
            sprintf('%04d-%02d-%02d 00:00:00', $fromParts[0], $fromParts[1], $fromParts[2]),
            sprintf('%04d-%02d-%02d 23:59:59', $toParts[0], $toParts[1], $toParts[2]),
        ];
    }

    private function jalaliToMiladiParts(string $value): ?array
    {
        $normalized = str_replace('/', '-', trim($value));
        $parts = explode('-', $normalized);

        if (count($parts) !== 3) {
            return null;
        }

        $miladi = Verta::jalaliToGregorian((int) $parts[0], (int) $parts[1], (int) $parts[2]);

        return [
            (int) $miladi[0],
            (int) str_pad((string) $miladi[1], 2, '0', STR_PAD_LEFT),
            (int) str_pad((string) $miladi[2], 2, '0', STR_PAD_LEFT),
        ];
    }

    private function formatDatatableRow(
        Pishfactor $factor,
        int $rowNumber,
        bool $showStoreColumn,
        bool $showVisitorColumn,
        bool $canDelete,
        string $csrf
    ): array {
        $infoUrl = route('pishFactorInfo', $factor->id);
        $packSum = (float) ($factor->items_pack_sum ?? 0);
        $tedadSum = (float) ($factor->items_tedad_sum ?? 0);
        $quantity = $packSum > 0 ? $packSum : $tedadSum;
        $unit = $packSum > 0
            ? ($factor->organization->sub_unit ?? '')
            : ($factor->organization->unit_order ?? '');
        $buyerName = $factor->is_agency_order
            ? ($factor->agencyUser->name ?? $factor->visitor->name ?? '')
            : ($factor->customer->name ?? '');
        $tablo = $factor->customer->tablo ?? '';
        $createdAt = Verta::instance($factor->created_at)->format('Y-m-d');
        $createdTime = Verta::instance($factor->created_at)->format('H:i');
        $price = number_format((int) str_replace(',', '', (string) $factor->fullPrice));

        $row = [
            '<input type="checkbox" class="actions" name="item_' . $factor->id . '" value="1" />',
            (string) $rowNumber,
            '<small data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ساعت ' . e($createdTime) . '">' . e($createdAt) . '</small>',
            '<small>' . e($factor->invoiceID) . '</small>',
        ];

        if ($showStoreColumn) {
            $row[] = '<small class="d-block badge bg-label-primary p-1 rounded mb-1" style="font-size:11px">'
                . e($factor->organization->title ?? '---') . '</small>';
        }

        $row = array_merge($row, [
            '<a href="' . $infoUrl . '">' . e($tablo) . '</a>',
            '<a href="' . $infoUrl . '">' . e($buyerName) . '</a>',
            '<small data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . (int) ($factor->items_count ?? 0) . ' قلم">' . number_format($quantity) . '</small>',
            e($unit),
            '<small>' . e($factor->recive_date ?: 'وارد نشده') . '</small>',
            '<small>' . $price . '</small>',
        ]);

        if ($showVisitorColumn) {
            $row[] = '<small>' . e($factor->visitor->name ?? '') . '</small>';
        }

        $row[] = '<small>' . e($factor->leader->name ?? '') . '</small>';
        $row[] = $this->statusBadge($factor);
        $row[] = $this->actionButtons($factor, $infoUrl, $canDelete, $csrf);

        return $row;
    }

    private function statusBadge(Pishfactor $factor): string
    {
        if ((int) $factor->status === 0) {
            return '<span class="badge bg-label-warning me-1">منتظر تایید</span>';
        }

        if ((int) $factor->status === 1) {
            return match ((int) $factor->step) {
                2 => '<small class="badge bg-label-success send_to_store_status me-1">تایید شده - ارسال به انبار</small>',
                3 => '<small class="badge bg-label-success shipment_status me-1">تایید شده - باربری و پخش</small>',
                4 => '<small class="badge bg-label-success arrived_status me-1">تایید شده - تحویل به مشتری</small>',
                default => '<small class="badge bg-label-success accepted_status me-1">تایید شده</small>',
            };
        }

        if ((int) $factor->status === 3) {
            return '<span class="badge bg-label-danger me-1">رد شده</span>';
        }

        if ((int) $factor->status === 5) {
            return '<span class="badge bg-label-warning me-1">مرجوعی</span>';
        }

        return '';
    }

    private function actionButtons(Pishfactor $factor, string $infoUrl, bool $canDelete, string $csrf): string
    {
        $viewIcon = '<a class="d-inline-block me-3" style="color:#248230;display:inline-flex" href="' . $infoUrl . '">'
            . \App\Support\UiIcon::html('eye') . '</a>';

        if (!$canDelete) {
            return $viewIcon;
        }

        $destroyUrl = route('pishfactor.destroy', $factor->id);

        return $viewIcon
            . '<form class="d-inline" action="' . $destroyUrl . '" method="POST" onsubmit="return confirm(\'آیا از حذف فاکتور مورد نظر اطمینان دارید؟\');">'
            . '<input type="hidden" name="_token" value="' . e($csrf) . '">'
            . '<button type="submit" class="d-inline" style="border:0 none;background:transparent;color:#C1292E;display:inline-flex">'
            . \App\Support\UiIcon::html('fa-trash') . '</button>'
            . '</form>';
    }
}
