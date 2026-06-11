<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>اسناد حسابداری - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('sweetalert::alert')
    @php
        $dimensionText = function ($item) {
            $parts = [];

            if ($item->costCenter) {
                $parts[] = 'مرکز هزینه: ' . trim(($item->costCenter->code ?: '') . ' ' . $item->costCenter->name);
            }

                                            <form id="merge-vouchers-form" action="{{ route('Accounting.vouchers.merge') }}" method="POST" class="d-flex flex-wrap gap-2">
                                                @csrf
                                                <input type="hidden" name="voucher_date_en" value="{{ now()->toDateString() }}">
                                                <input type="hidden" name="description" value="ادغام اسناد موقت انتخاب شده">
                                                <button type="submit" class="btn btn-outline-warning">
                                                    <i class="ti ti-git-merge me-1"></i> ادغام انتخابی
                                                </button>
                                            </form>
            if ($item->branch) {
                $parts[] = 'شعبه: ' . $item->branch->title;
            }

            if ($item->project_code) {
                $parts[] = 'پروژه: ' . $item->project_code;
            }

            if ($item->product) {
                $parts[] = 'کالا: ' . trim($item->product->title . ' ' . $item->product->display_name);
            }

            if ($item->customer) {
                $parts[] = 'مشتری: ' . ($item->customer->name ?: $item->customer->tablo);
            }

            if ($item->employee) {
                $parts[] = 'کارمند: ' . $item->employee->name;
            }

            if ($item->contract_code) {
                $parts[] = 'قرارداد: ' . $item->contract_code;
            }

            if ($item->route_code) {
                $parts[] = 'مسیر: ' . $item->route_code;
            }

            return implode(' | ', array_filter($parts));
        };
    @endphp
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> اسناد حسابداری
                            </h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.analyticDimensions') }}">
                                    <i class="ti ti-chart-dots me-1"></i> گزارش تفصیل شناور
                                </a>
                                <a class="btn btn-primary" href="{{ route('Accounting.vouchers.create') }}">
                                    <i class="ti ti-plus me-1"></i> ثبت سند جدید
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-datatable table-responsive py-0">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>ادغام</th>
                                            <th>شماره سند</th>
                                            <th>تاریخ</th>
                                            <th>شرح</th>
                                            <th>جمع بدهکار</th>
                                            <th>جمع بستانکار</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($vouchers as $voucher)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->iteration + ($vouchers->currentPage() - 1) * $vouchers->perPage() }}
                                                </td>
                                                <td class="text-center">
                                                    @if (!$voucher->is_permanent && $voucher->status === 'draft' && in_array($voucher->document_type, ['manual', 'manual_copy', 'manual_template', 'manual_merge'], true))
                                                        <input class="form-check-input merge-voucher-id" type="checkbox" value="{{ $voucher->id }}">
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $voucher->voucher_number }}</td>
                                                <td>{{ $voucher->voucher_date_fa ?: optional($voucher->voucher_date_en)->format('Y-m-d') }}
                                                </td>
                                                <td>{{ $voucher->description }}</td>
                                                <td class="text-end">{{ number_format((float) $voucher->total_debit) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $voucher->total_credit) }}</td>
                                                <td class="text-center">
                                                    @if ($voucher->status === 'cancelled')
                                                        <span class="badge bg-label-secondary">ابطال شده</span>
                                                    @elseif ($voucher->status === 'reversed')
                                                        <span class="badge bg-label-danger">برگشت خورده</span>
                                                    @elseif ($voucher->document_type === 'voucher_reversal')
                                                        <span class="badge bg-label-info">سند برگشتی</span>
                                                    @elseif ($voucher->is_permanent)
                                                        <span class="badge bg-label-success">دائمی</span>
                                                    @else
                                                        <span class="badge bg-label-warning">موقت</span>
                                                    @endif
                                                    @if ($voucher->originalVoucher)
                                                        <div class="small text-muted mt-1">اصل:
                                                            {{ $voucher->originalVoucher->voucher_number }}</div>
                                                    @elseif ($voucher->reversalVoucher)
                                                        <div class="small text-muted mt-1">برگشتی:
                                                            {{ $voucher->reversalVoucher->voucher_number }}</div>
                                                    @elseif ($voucher->mergedIntoVoucher)
                                                        <div class="small text-muted mt-1">ادغام در:
                                                            {{ $voucher->mergedIntoVoucher->voucher_number }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($voucher->status === 'cancelled')
                                                        <span class="text-muted">ابطال شده</span>
                                                    @else
                                                        <form
                                                            action="{{ route('Accounting.vouchers.copy', $voucher) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="voucher_date_en"
                                                                value="{{ now()->toDateString() }}">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-primary">کپی</button>
                                                        </form>
                                                        <form
                                                            action="{{ route('Accounting.vouchers.template', $voucher) }}"
                                                            method="POST"
                                                            class="d-inline-flex align-items-center gap-1 mt-1">
                                                            @csrf
                                                            <input type="hidden" name="frequency" value="on_demand">
                                                            <input type="text" name="name"
                                                                class="form-control form-control-sm"
                                                                style="width: 130px"
                                                                value="الگوی {{ $voucher->voucher_number }}">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-dark">الگو</button>
                                                        </form>
                                                    @endif

                                                    @if ($voucher->status === 'cancelled')
                                                    @elseif (!$voucher->is_permanent)
                                                        @if (in_array($voucher->document_type, ['manual', 'manual_copy', 'manual_template'], true))
                                                            <a class="btn btn-sm btn-outline-info"
                                                                href="{{ route('Accounting.vouchers.edit', $voucher) }}">ویرایش</a>
                                                        @endif
                                                        <form
                                                            action="{{ route('Accounting.vouchers.permanent', $voucher) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-success">دائمی کن</button>
                                                        </form>
                                                        <form
                                                            action="{{ route('Accounting.vouchers.cancel', $voucher) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="return confirm('این سند موقت ابطال شود؟')">ابطال</button>
                                                        </form>
                                                    @elseif ($voucher->status === 'reversed' || $voucher->document_type === 'voucher_reversal')
                                                        <span class="text-muted">ثبت نهایی</span>
                                                    @else
                                                        <form
                                                            action="{{ route('Accounting.vouchers.reverse', $voucher) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="voucher_date_en"
                                                                value="{{ now()->toDateString() }}">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('برای این سند، سند برگشتی معکوس ثبت شود؟')">برگشت
                                                                سند</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($voucher->items->isNotEmpty())
                                                <tr>
                                                    <td></td>
                                                    <td colspan="7">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>حساب</th>
                                                                        <th>شرح ردیف</th>
                                                                        <th>تفصیل شناور</th>
                                                                        <th class="text-end">بدهکار</th>
                                                                        <th class="text-end">بستانکار</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($voucher->items as $item)
                                                                        <tr>
                                                                            <td>{{ optional($item->account)->code }} -
                                                                                {{ optional($item->account)->name }}
                                                                            </td>
                                                                            <td>{{ $item->description }}</td>
                                                                            <td>
                                                                                @if ($dimensionText($item))
                                                                                    <span
                                                                                        class="small text-muted">{{ $dimensionText($item) }}</span>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-end">
                                                                                {{ number_format((float) $item->debit_amount) }}
                                                                            </td>
                                                                            <td class="text-end">
                                                                                {{ number_format((float) $item->credit_amount) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">هنوز سند
                                                    حسابداری
                                                    ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $vouchers->links() }}
                        </div>
                    </div>
                    @include('sections/footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        document.getElementById('merge-vouchers-form')?.addEventListener('submit', function(event) {
            this.querySelectorAll('input[name="voucher_ids[]"]').forEach((input) => input.remove());
            const checked = document.querySelectorAll('.merge-voucher-id:checked');

            if (checked.length < 2) {
                event.preventDefault();
                alert('برای ادغام حداقل دو سند موقت را انتخاب کنید.');
                return;
            }

            checked.forEach((checkbox) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'voucher_ids[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });
    </script>
</body>

</html>
