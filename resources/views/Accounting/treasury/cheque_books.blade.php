<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>دسته چک و هشدارها - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> دسته چک و
                                هشدارها</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-primary" href="{{ route('Accounting.treasury.create') }}">ثبت
                                    پرداخت چکی</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">چک معوق باز</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($alerts['summary']['overdue_count']) }}</h4><small
                                            class="text-danger">{{ number_format($alerts['summary']['overdue_amount']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">سررسید {{ $alerts['days'] }} روز
                                            آینده</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($alerts['summary']['upcoming_count']) }}</h4><small
                                            class="text-warning">{{ number_format($alerts['summary']['upcoming_amount']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">دسته چک کم برگ</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($alerts['summary']['low_leaf_book_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">برگ آماده مصرف</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($alerts['summary']['available_leaf_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.treasury.chequeBooks.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف دسته چک</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12"><label class="form-label">حساب بانکی</label><select
                                                name="account_id" class="form-select account-select" required>
                                                <option value="">انتخاب حساب</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6"><label class="form-label">شماره دسته</label><input
                                                type="text" name="book_number" class="form-control"></div>
                                        <div class="col-12 col-md-6"><label class="form-label">پیشوند برگ</label><input
                                                type="text" name="cheque_prefix" class="form-control"></div>
                                        <div class="col-12 col-md-6"><label class="form-label">اولین شماره</label><input
                                                type="number" min="1" name="first_leaf_number"
                                                class="form-control text-end" required></div>
                                        <div class="col-12 col-md-6"><label class="form-label">آخرین شماره</label><input
                                                type="number" min="1" name="last_leaf_number"
                                                class="form-control text-end" required></div>
                                        <div class="col-12 col-md-6"><label class="form-label">حد هشدار
                                                برگ</label><input type="number" min="0" max="100"
                                                name="warning_threshold" class="form-control text-end"
                                                value="5"></div>
                                        <div class="col-12 col-md-6"><label class="form-label">وضعیت</label><select
                                                name="status" class="form-select">
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                                <option value="finished">تمام شده</option>
                                            </select></div>
                                        <div class="col-12 col-md-6"><label class="form-label">بانک</label><input
                                                type="text" name="bank_name" class="form-control"></div>
                                        <div class="col-12 col-md-6"><label class="form-label">شعبه</label><input
                                                type="text" name="branch_name" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">شماره حساب</label><input
                                                type="text" name="account_number" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">شرح</label><input
                                                type="text" name="description" class="form-control"></div>
                                    </div>
                                    <div class="card-footer text-end"><button class="btn btn-primary"
                                            type="submit">ثبت دسته چک</button></div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-8">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">دسته چک ها</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>دسته</th>
                                                    <th>حساب</th>
                                                    <th>بازه</th>
                                                    <th class="text-end">آماده</th>
                                                    <th class="text-end">صادر شده</th>
                                                    <th>هشدار</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($books as $book)
                                                    <tr>
                                                        <td>{{ $book->book_number ?: $book->id }}<br><small>{{ $book->bank_name ?: '-' }}</small>
                                                        </td>
                                                        <td>{{ optional($book->account)->code }} -
                                                            {{ optional($book->account)->name }}</td>
                                                        <td>{{ $book->cheque_prefix }}{{ $book->first_leaf_number }}
                                                            تا {{ $book->cheque_prefix }}{{ $book->last_leaf_number }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((int) $book->available_leaves_count) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((int) $book->issued_leaves_count) }}</td>
                                                        <td>{{ (int) $book->available_leaves_count <= (int) $book->warning_threshold ? 'کمتر از حد مجاز' : '-' }}
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-{{ $book->status === 'active' ? 'success' : ($book->status === 'finished' ? 'warning' : 'secondary') }}">{{ $book->status === 'active' ? 'فعال' : ($book->status === 'finished' ? 'تمام شده' : 'غیرفعال') }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">دسته
                                                            چکی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">هشدار سررسید چک</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>چک</th>
                                                    <th>جهت</th>
                                                    <th>سررسید</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th>طرف حساب</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($alerts['overdue_cheques']->merge($alerts['upcoming_cheques'])->take(20) as $cheque)
                                                    <tr>
                                                        <td>{{ $cheque->cheque_number }}</td>
                                                        <td>{{ $cheque->direction === 'incoming' ? 'دریافتنی' : 'پرداختنی' }}
                                                        </td>
                                                        <td>{{ optional($cheque->due_date)->format('Y-m-d') }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $cheque->amount) }}</td>
                                                        <td>{{ optional($cheque->counterAccount)->name ?: '-' }}</td>
                                                </tr>@empty<tr>
                                                        <td colspan="5" class="text-center text-muted py-4">هشدار
                                                            سررسید فعالی وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">هشدار کمبود برگ چک</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>دسته</th>
                                                    <th>حساب</th>
                                                    <th class="text-end">برگ آماده</th>
                                                    <th class="text-end">حد هشدار</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($alerts['low_leaf_books'] as $book)
                                                    <tr>
                                                        <td>{{ $book->book_number ?: $book->id }}</td>
                                                        <td>{{ optional($book->account)->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((int) $book->available_leaves_count) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((int) $book->warning_threshold) }}</td>
                                                </tr>@empty<tr>
                                                        <td colspan="4" class="text-center text-muted py-4">کمبود
                                                            برگ چک دیده نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form class="card mb-4" method="GET"
                            action="{{ route('Accounting.treasury.chequeBooks') }}">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-4"><label class="form-label">دسته چک</label><select
                                        name="book_id" class="form-select">
                                        <option value="">همه</option>
                                        @foreach ($books as $book)
                                            <option value="{{ $book->id }}" @selected(request('book_id') == $book->id)>
                                                {{ $book->book_number ?: $book->id }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="col-12 col-md-4"><label class="form-label">وضعیت برگ</label><select
                                        name="status" class="form-select">
                                        <option value="">همه</option>
                                        <option value="available" @selected(request('status') === 'available')>آماده</option>
                                        <option value="issued" @selected(request('status') === 'issued')>صادر شده</option>
                                        <option value="blocked" @selected(request('status') === 'blocked')>مسدود</option>
                                    </select></div>
                                <div class="col-12 col-md-2"><label class="form-label">افق هشدار</label><input
                                        type="number" min="1" max="60" name="alert_days"
                                        class="form-control" value="{{ request('alert_days', $alerts['days']) }}">
                                </div>
                                <div class="col-12 col-md-2"><button class="btn btn-primary w-100"
                                        type="submit">فیلتر</button></div>
                            </div>
                        </form>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">برگ های چک</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره برگ</th>
                                            <th>دسته</th>
                                            <th>حساب</th>
                                            <th>وضعیت</th>
                                            <th>سررسید</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>طرف حساب</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leaves as $leaf)
                                            <tr>
                                                <td>{{ $leaf->leaf_number }}</td>
                                                <td>{{ optional($leaf->book)->book_number ?: '-' }}</td>
                                                <td>{{ optional($leaf->account)->code }} -
                                                    {{ optional($leaf->account)->name }}</td>
                                                <td>{{ ['available' => 'آماده', 'issued' => 'صادر شده', 'blocked' => 'مسدود', 'voided' => 'باطل', 'cancelled' => 'لغو شده'][$leaf->status] ?? $leaf->status }}
                                                </td>
                                                <td>{{ optional($leaf->due_date)->format('Y-m-d') ?: '-' }}</td>
                                                <td class="text-end">{{ number_format((float) $leaf->amount) }}</td>
                                                <td>{{ optional($leaf->instrument?->counterAccount)->name ?: '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">برگی با فیلتر
                                                    انتخاب شده پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">{{ $leaves->links() }}</div>
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.account-select').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>
