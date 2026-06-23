<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ورود گروهی محصولات - دکان دارمینو</title>
    <meta content="" name="description"/>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections.sidebar')
        <div class="layout-page">
            @include('sections.header')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4" id="tour-import-page-header">
                        <div id="tour-import-page-title">
                            <h4 class="mb-1">
                                <span class="text-muted fw-light">محصولات /</span>
                                عملیات ورود دیتا
                            </h4>
                            <div class="text-muted">ورود گروهی محصول از فایل CSV یا Excel — کلید یکتا محصول در پنل: SKU.</div>
                        </div>
                        <div>
                            <a class="btn btn-label-secondary" id="tour-import-template" href="{{ route('products.data-import.template') }}">
                                <x-ui.icon name="download" class="me-1" />دانلود قالب CSV
                            </a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card h-100" id="tour-import-upload-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">آپلود فایل</h5>
                                </div>
                                <div class="card-body">
                                    <form id="product-import-form" method="POST" action="{{ route('products.data-import.import') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3" id="tour-import-file">
                                            <label class="form-label" for="import-file">فایل ورودی</label>
                                            <input type="file" name="file" id="import-file" class="form-control @error('file') is-invalid @enderror"
                                                accept=".csv,.txt,.xlsx,.xls" required>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                فرمت‌های مجاز: CSV، TXT، XLSX، XLS — حداکثر ۱۰ مگابایت
                                            </div>
                                        </div>

                                        <div class="form-check mb-2" id="tour-import-update-existing">
                                            <input class="form-check-input" type="checkbox" name="update_existing" value="1" id="update_existing"
                                                @checked(old('update_existing'))>
                                            <label class="form-check-label fw-semibold" for="update_existing">به‌روزرسانی محصول موجود</label>
                                        </div>
                                        <p class="small text-muted mb-4">
                                            اگر SKU در سیستم وجود دارد، با فعال بودن این گزینه اطلاعات محصول <strong>به‌روزرسانی</strong> می‌شود؛
                                            در غیر این صورت ردیف رد می‌شود. برای import اولیه معمولاً لازم نیست.
                                        </p>

                                        <button type="submit" class="btn btn-primary" id="tour-import-submit">
                                            <x-ui.icon name="upload" class="me-1" />شروع Import (صف)
                                        </button>

                                        <div id="tour-import-progress" class="import-progress-panel d-none mt-4" aria-live="polite">
                                            <div class="d-flex justify-content-between align-items-center small mb-2">
                                                <span class="fw-semibold" id="import-progress-label">آماده‌سازی...</span>
                                                <span class="text-muted" id="import-progress-percent">0%</span>
                                            </div>
                                            <div class="progress import-progress-bar-wrap">
                                                <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                    role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mt-3" id="import-progress-steps">
                                                <span class="badge bg-label-secondary import-step" data-step="upload">۱. آپلود فایل</span>
                                                <span class="badge bg-label-secondary import-step" data-step="queue">۲. صف پردازش</span>
                                                <span class="badge bg-label-secondary import-step" data-step="reading">۳. خواندن فایل</span>
                                                <span class="badge bg-label-secondary import-step" data-step="importing">۴. ثبت محصولات</span>
                                                <span class="badge bg-label-secondary import-step" data-step="completed">۵. پایان</span>
                                            </div>
                                            <div class="small text-muted mt-2" id="import-progress-detail"></div>
                                            <div id="import-error-details" class="import-error-details d-none">
                                                <div class="alert alert-warning mb-0 py-2 px-3">
                                                    <div class="fw-semibold small mb-2">جزئیات خطاها</div>
                                                    <ul class="import-error-list small mb-0" id="import-error-list"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card h-100" id="tour-import-guide-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">راهنمای ستون‌ها</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info py-2 px-3 mb-3 small">
                                        <strong>قوانین کلی:</strong>
                                        <ul class="mb-0 mt-2 pe-3">
                                            <li>فایل باید سرستون فارسی مطابق <strong>قالب CSV</strong> داشته باشد.</li>
                                            <li>ستون‌های <strong>عنوان</strong> و <strong>SKU</strong> برای هر ردیف معتبر <strong>الزامی</strong> هستند (اگر SKU خالی باشد از عنوان ساخته می‌شود).</li>
                                            <li>ردیف‌هایی که <strong>هم عنوان و هم SKU خالی</strong> باشند نادیده گرفته می‌شوند.</li>
                                            <li><strong>واحد اصلی</strong> و <strong>واحد فرعی</strong> باید با نام یا شناسه واحدهای ثبت‌شده در منوی «واحدهای محصول» هم‌خوان باشند.</li>
                                            <li>ستون‌های <strong>انبار</strong> و <strong>موجودی اولیه</strong> فقط در پنل‌های دارای ماژول انبار کاربرد دارند — در سایر پنل‌ها خالی بگذارید.</li>
                                        </ul>
                                    </div>

                                    <h6 class="fw-semibold mb-2">ستون‌های فایل ({{ count($importColumnGuide) }} ستون)</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ستون</th>
                                                    <th>الزام</th>
                                                    <th>توضیح</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($importColumnGuide as $column)
                                                    <tr>
                                                        <td class="fw-semibold text-nowrap">{{ $column['header'] }}</td>
                                                        <td class="text-nowrap">
                                                            @if($column['required'])
                                                                <span class="badge bg-label-danger">الزامی</span>
                                                            @else
                                                                <span class="badge bg-label-secondary">اختیاری</span>
                                                            @endif
                                                        </td>
                                                        <td class="small text-muted">{{ $column['note'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <h6 class="fw-semibold mb-2">شناسایی محصول</h6>
                                    <ul class="mb-3 ps-3 small">
                                        <li class="mb-1"><strong>SKU</strong> کلید یکتا محصول در همین پنل است.</li>
                                        <li class="mb-1">با فعال کردن «به‌روزرسانی محصول موجود»، ردیف‌های با SKU تکراری به‌روز می‌شوند.</li>
                                        <li class="mb-1">فایل آپلودشده پس از پردازش از سرور <strong>حذف</strong> می‌شود.</li>
                                    </ul>

                                    <p class="small text-muted mb-0">
                                        <x-ui.icon name="info-circle" class="me-1" />
                                        پردازش در صف انجام می‌شود؛ وضعیت در جدول زیر قابل پیگیری است.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4" id="tour-import-history">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">آخرین عملیات ورود</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>وضعیت</th>
                                        <th>نام فایل</th>
                                        <th>موفق / کل</th>
                                        <th>خطا</th>
                                        <th>تاریخ</th>
                                        <th>جزئیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentImports as $import)
                                        @php
                                            $statusClass = match ($import->status) {
                                                'completed' => 'success',
                                                'processing' => 'info',
                                                'completed_with_errors' => 'warning',
                                                'failed' => 'danger',
                                                default => 'secondary',
                                            };
                                            $statusLabel = match ($import->status) {
                                                'completed' => 'تکمیل',
                                                'processing' => 'در حال پردازش',
                                                'completed_with_errors' => 'تکمیل با خطا',
                                                'failed' => 'ناموفق',
                                                default => $import->status,
                                            };
                                            $errorSamples = $importService->summarizeRowErrors($import->summary_json ?? []);
                                            $hasErrorDetails = $import->error_message || $errorSamples !== [];
                                        @endphp
                                        <tr data-import-id="{{ $import->id }}" @class(['import-processing' => $import->status === 'processing'])>
                                            <td>{{ $import->id }}</td>
                                            <td><span class="badge bg-label-{{ $statusClass }} import-status">{{ $statusLabel }}</span></td>
                                            <td>{{ $import->file_name ?: '—' }}</td>
                                            <td class="import-counts">{{ $import->success_rows }}/{{ $import->total_rows ?: '—' }}</td>
                                            <td class="import-failed">{{ $import->failed_rows ?: '—' }}</td>
                                            <td>{{ verta_datetime($import->started_at) }}</td>
                                            <td>
                                                @if ($hasErrorDetails)
                                                    <button type="button"
                                                        class="btn btn-sm btn-label-secondary import-history-detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#import-history-detail-modal"
                                                        data-import-label="#{{ $import->id }} — {{ $import->file_name ?: 'بدون نام فایل' }}"
                                                        data-import-status="{{ $statusLabel }}"
                                                        data-import-summary="{{ $import->error_message ?: sprintf('%s موفق / %s خطا از %s ردیف', number_format((int) $import->success_rows), number_format((int) $import->failed_rows), number_format((int) $import->total_rows)) }}"
                                                        data-import-errors='@json($errorSamples, JSON_UNESCAPED_UNICODE)'>
                                                        <x-ui.icon name="list-details" class="me-1" />خطاها
                                                    </button>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">هنوز عملیات ورودی ثبت نشده است.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('sections.footer')
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
</div>

<div class="modal fade" id="import-history-detail-modal" tabindex="-1" aria-labelledby="import-history-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="import-history-detail-title">جزئیات خطای Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">عملیات</div>
                    <div class="fw-semibold" id="import-history-detail-label">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small mb-1">وضعیت</div>
                    <div id="import-history-detail-status">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small mb-1">خلاصه</div>
                    <div id="import-history-detail-summary">—</div>
                </div>
                <div>
                    <div class="text-muted small mb-2">نمونه خطاها</div>
                    <ul class="import-error-list small mb-0" id="import-history-detail-errors"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

@include('partials.panel-toasts')

<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>
<script>
    $(function () {
        const statusUrl = @json(route('products.data-import.status', ['run' => '__RUN__']));
        const importUrl = @json(route('products.data-import.import'));
        const csrfToken = @json(csrf_token());

        const statusLabels = {
            completed: 'تکمیل',
            processing: 'در حال پردازش',
            completed_with_errors: 'تکمیل با خطا',
            failed: 'ناموفق',
        };
        const statusClasses = {
            completed: 'success',
            processing: 'info',
            completed_with_errors: 'warning',
            failed: 'danger',
        };

        const $form = $('#product-import-form');
        const $panel = $('#tour-import-progress');
        const $bar = $('#import-progress-bar');
        const $label = $('#import-progress-label');
        const $percent = $('#import-progress-percent');
        const $detail = $('#import-progress-detail');
        const $submitBtn = $('#tour-import-submit');
        const $errorDetails = $('#import-error-details');
        const $errorList = $('#import-error-list');

        function formatErrorSample(sample) {
            const lines = Array.isArray(sample.lines) && sample.lines.length
                ? ' — سطرهای ' + sample.lines.join('، ')
                : '';

            return sample.message + ' (' + sample.count + ' مورد)' + lines;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderImportErrorList($target, samples, fallbackMessage) {
            $target.empty();

            if (Array.isArray(samples) && samples.length) {
                samples.forEach(function (sample) {
                    $target.append($('<li></li>').text(formatErrorSample(sample)));
                });
                return;
            }

            if (fallbackMessage) {
                $target.append($('<li></li>').text(fallbackMessage));
            }
        }

        function renderImportErrorDetails(data) {
            const hasIssues = data.status === 'failed' || data.status === 'completed_with_errors';
            const samples = data.error_samples || [];

            if (!hasIssues) {
                $errorDetails.addClass('d-none');
                $errorList.empty();
                return;
            }

            renderImportErrorList($errorList, samples, data.error_message || data.detail_message || null);
            $errorDetails.removeClass('d-none');
        }

        function buildImportToastHtml(data) {
            const title = data.status === 'completed_with_errors'
                ? 'تکمیل با خطا'
                : (data.status === 'failed' ? 'Import ناموفق' : 'نتیجه Import');
            const detail = buildImportDetail(data);
            let html = '<strong>' + title + '</strong><br>' + detail;

            if (Array.isArray(data.error_samples) && data.error_samples.length) {
                html += '<ul style="margin:0.5rem 0 0;padding-right:1rem;">';
                data.error_samples.slice(0, 4).forEach(function (sample) {
                    html += '<li>' + escapeHtml(formatErrorSample(sample)) + '</li>';
                });
                html += '</ul>';
            }

            return html;
        }

        function setProgress(percent, label, stage, detail) {
            const safePercent = Math.max(0, Math.min(100, percent));
            $bar.css('width', safePercent + '%').attr('aria-valuenow', safePercent);
            $percent.text(safePercent + '%');
            if (label) $label.text(label);
            if (arguments.length >= 4) $detail.text(detail || '');

            const visualStage = stage === 'completed_with_errors' ? 'failed' : stage;

            $('.import-step').each(function () {
                const step = $(this).data('step');
                $(this).removeClass('bg-label-primary bg-label-success bg-label-secondary bg-label-danger');
                if (visualStage === 'failed') {
                    $(this).addClass(step === 'completed' ? 'bg-label-danger' : 'bg-label-secondary');
                    return;
                }
                if (step === stage) {
                    $(this).addClass(safePercent >= 100 ? 'bg-label-success' : 'bg-label-primary');
                } else {
                    const order = ['upload', 'queue', 'reading', 'importing', 'completed'];
                    const currentIndex = order.indexOf(stage);
                    const stepIndex = order.indexOf(step);
                    $(this).addClass(stepIndex < currentIndex ? 'bg-label-success' : 'bg-label-secondary');
                }
            });
        }

        function buildImportDetail(data) {
            if (data.detail_message) {
                return data.detail_message;
            }

            if (data.error_message) {
                return data.error_message;
            }

            if (data.status === 'failed') {
                return 'پردازش فایل ناموفق بود.';
            }

            return (data.success_rows ?? 0) + ' از ' + (data.total_rows ?? 0) + ' ردیف پردازش شد';
        }

        function notifyImportResult(data) {
            if (!window.DokanToast) {
                return;
            }

            if (data.status === 'completed') {
                DokanToast.success('فایل با موفقیت پردازش شد — ' + (data.success_rows ?? 0) + ' از ' + (data.total_rows ?? 0) + ' ردیف ثبت شد.');
                return;
            }

            if (data.status === 'completed_with_errors') {
                DokanToast.warning(buildImportToastHtml(data), { html: true, duration: 9000 });
                return;
            }

            if (data.status === 'failed') {
                DokanToast.error(buildImportToastHtml(data), { html: true, duration: 9000 });
            }
        }

        function finishImportProgress(data, reloadAfter) {
            const stage = data.status === 'completed'
                ? 'completed'
                : (data.status === 'completed_with_errors' ? 'completed_with_errors' : 'failed');
            const detail = buildImportDetail(data);

            setProgress(
                data.progress_percent ?? 100,
                data.stage_label || statusLabels[data.status] || data.status,
                stage,
                detail
            );

            $bar.removeClass('progress-bar-animated progress-bar-striped');
            if (data.status === 'failed' || data.status === 'completed_with_errors') {
                $bar.addClass('bg-danger');
            } else {
                $bar.removeClass('bg-danger');
            }

            $submitBtn.prop('disabled', false);
            renderImportErrorDetails(data);
            notifyImportResult(data);

            if (reloadAfter) {
                setTimeout(function () { window.location.reload(); }, 3200);
            }
        }

        function pollRunStatus(runId, onDone) {
            const poll = function () {
                $.getJSON(statusUrl.replace('__RUN__', runId), function (data) {
                    const serverPercent = data.progress_percent ?? 30;
                    const mappedPercent = Math.max(22, Math.min(99, serverPercent));
                    setProgress(
                        mappedPercent,
                        data.stage_label || 'در حال پردازش...',
                        data.stage || 'importing',
                        buildImportDetail(data)
                    );

                    if (data.status === 'processing') {
                        setTimeout(poll, 1500);
                        return;
                    }

                    finishImportProgress(data, false);

                    if (typeof onDone === 'function') {
                        onDone(data);
                    }
                }).fail(function () {
                    setTimeout(poll, 2500);
                });
            };

            poll();
        }

        $form.on('submit', function (event) {
            event.preventDefault();

            const fileInput = document.getElementById('import-file');
            if (!fileInput || !fileInput.files.length) {
                return;
            }

            const formData = new FormData(this);
            $panel.removeClass('d-none');
            $errorDetails.addClass('d-none');
            $errorList.empty();
            $submitBtn.prop('disabled', true);
            $bar.addClass('progress-bar-animated progress-bar-striped');
            setProgress(2, 'آماده‌سازی آپلود...', 'upload', fileInput.files[0].name);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', importUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.upload.addEventListener('progress', function (e) {
                if (!e.lengthComputable) return;
                const uploadPercent = Math.round((e.loaded / e.total) * 18) + 2;
                setProgress(uploadPercent, 'در حال آپلود فایل...', 'upload', Math.round((e.loaded / e.total) * 100) + '% از حجم فایل');
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;

                if (xhr.status >= 200 && xhr.status < 300) {
                    let payload = {};
                    try {
                        payload = JSON.parse(xhr.responseText);
                    } catch (error) {
                        window.location.reload();
                        return;
                    }

                    setProgress(22, 'قرارگیری در صف پردازش...', 'queue', 'فایل دریافت شد');

                    if (payload.status === 'processing') {
                        setTimeout(function () {
                            pollRunStatus(payload.run_id, function () {
                                setTimeout(function () { window.location.reload(); }, 1800);
                            });
                        }, 400);
                        return;
                    }

                    finishImportProgress(payload, true);
                    return;
                }

                let message = 'آپلود یا پردازش فایل ناموفق بود.';
                try {
                    const errors = JSON.parse(xhr.responseText);
                    if (errors.message) message = errors.message;
                    if (errors.errors && errors.errors.file) message = errors.errors.file[0];
                } catch (error) {}

                setProgress(100, 'خطا در Import', 'failed', message);
                $bar.removeClass('progress-bar-animated progress-bar-striped').addClass('bg-danger');
                $submitBtn.prop('disabled', false);
                if (window.DokanToast) DokanToast.error(message);
            };

            xhr.send(formData);
        });

        $('tr.import-processing').each(function () {
            const row = $(this);
            const runId = row.data('import-id');
            pollRunStatus(runId, function (data) {
                const badge = row.find('.import-status');
                badge.text(statusLabels[data.status] || data.status);
                badge.removeClass('bg-label-success bg-label-info bg-label-warning bg-label-danger bg-label-secondary');
                badge.addClass('bg-label-' + (statusClasses[data.status] || 'secondary'));
                row.find('.import-counts').text((data.success_rows ?? '—') + '/' + (data.total_rows ?? '—'));
                row.find('.import-failed').text(data.failed_rows || '—');
                row.removeClass('import-processing');
            });
        });

        $('#import-history-detail-modal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const errors = button.data('importErrors') || [];
            const label = button.data('importLabel') || '—';
            const status = button.data('importStatus') || '—';
            const summary = button.data('importSummary') || '—';

            $('#import-history-detail-label').text(label);
            $('#import-history-detail-status').text(status);
            $('#import-history-detail-summary').text(summary);
            renderImportErrorList($('#import-history-detail-errors'), errors, summary);
        });
    });
</script>
</body>

</html>
