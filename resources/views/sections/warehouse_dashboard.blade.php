@if (
    ($warehouseDashboard['enabled'] ?? false) &&
        (auth()->user()->isGod == 1 ||
            auth()->user()->isAdmin == 1 ||
            auth()->user()->roles->contains('title', 'store')))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">پایش انبار</h5>
                        <small class="text-muted">هشدارهای موجودی، گردش های اخیر و کالاهای نزدیک کمبود</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-label-primary" href="{{ route('stocks.inventoryBalances') }}">موجودی لحظه
                            ای</a>
                        <a class="btn btn-sm btn-label-secondary" href="{{ route('stocks.inventoryMovements') }}">دفتر
                            گردش</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        @foreach ($warehouseDashboard['alerts'] as $alert)
                            <div class="col-12 col-md-4">
                                <div class="alert alert-{{ $alert['type'] }} mb-0 h-100">
                                    <strong>{{ $alert['title'] }}</strong>
                                    <div>{{ $alert['body'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <h6 class="mb-3">کالاهای کم یا نزدیک منفی</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th>انبار</th>
                                            <th>مکان</th>
                                            <th>موجودی</th>
                                            <th>حد سفارش</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($warehouseDashboard['low_stock_items'] as $balance)
                                            <tr>
                                                <td>
                                                    @if ($balance->product)
                                                        <a href="{{ route('stocks.PrCartex', $balance->product_id) }}">{{ $balance->product->title }}
                                                            {{ $balance->product->display_name }}</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ optional($balance->store)->title ?: '-' }}</td>
                                                <td>{{ optional($balance->warehouseLocation)->path ?: 'بدون مکان' }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ (float) $balance->quantity < 0 ? 'danger' : 'warning' }}">
                                                        {{ number_format((float) $balance->quantity, 3) }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($balance->product)->orderLimit ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="5">فعلا هشدار کمبود موجودی ثبت نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <h6 class="mb-3">آخرین گردش ها</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>کالا</th>
                                            <th>نوع</th>
                                            <th>مقدار</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($warehouseDashboard['recent_movements'] as $movement)
                                            <tr>
                                                <td>{{ optional($movement->occurred_at)->format('Y-m-d') ?: '-' }}</td>
                                                <td>{{ optional($movement->product)->title ?: '-' }}</td>
                                                <td>{{ $movement->direction === 'out' ? 'خروج' : 'ورود' }}</td>
                                                <td>{{ number_format((float) $movement->quantity, 3) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="4">گردشی ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
