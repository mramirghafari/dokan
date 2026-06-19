<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>Integrationهای CRM - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />`n<script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .integration-kpis {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .integration-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ltr-chip {
            direction: ltr;
            unicode-bidi: plaintext;
            white-space: normal;
        }

        @media (max-width: 1200px) {
            .integration-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {

            .integration-kpis,
            .integration-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> Integrationها</h4>
                                <div class="text-muted">VoIP، تقویم کاری و فایل بیرونی با token، sync log و audit قابل
                                    پیگیری.</div>
                            </div>
                            <a class="btn btn-outline-primary" href="{{ route('crm.call-center.index') }}">مرکز تماس
                                CRM</a>
                        </div>

                        <div class="integration-kpis mb-4">
                            <div class="card">
                                <div class="card-body"><span>اتصال فعال</span>
                                    <h3 class="mt-2 mb-0 text-primary">
                                        {{ number_format($stats['active_connections']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>VoIP</span>
                                    <h3 class="mt-2 mb-0 text-info">{{ number_format($stats['voip']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>تقویم</span>
                                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['calendar']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>Drive</span>
                                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['drive']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>خطای sync</span>
                                    <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['failed_logs']) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف connection</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.integrations.store') }}">
                                            @csrf
                                            <div class="integration-grid">
                                                <div><label class="form-label">نوع</label><select class="form-select"
                                                        name="type" required>
                                                        @foreach ($types as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">Provider</label><select
                                                        class="form-select" name="provider" required>
                                                        @foreach ($providers as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">عنوان</label><input class="form-control"
                                                        name="title" required placeholder="مثلا Google Calendar فروش">
                                                </div>
                                                <div><label class="form-label">Endpoint خروجی</label><input
                                                        class="form-control ltr-chip" name="endpoint_url"
                                                        placeholder="https://provider.example/api/sync"></div>
                                                <div><label class="form-label">Token خروجی</label><input
                                                        class="form-control ltr-chip" name="outbound_auth_token"
                                                        placeholder="Bearer token فقط رمزنگاری شده ذخیره می شود"></div>
                                                <div><label class="form-label">Webhook secret</label><input
                                                        class="form-control ltr-chip" name="webhook_secret"
                                                        placeholder="خالی بماند خودکار ساخته می شود"></div>
                                                <div><label class="form-label">خط/داخلی VoIP</label><input
                                                        class="form-control" name="voip_line" placeholder="مثلا 301">
                                                </div>
                                                <div><label class="form-label">Context VoIP</label><input
                                                        class="form-control" name="voip_context"
                                                        placeholder="from-internal"></div>
                                                <div><label class="form-label">Caller ID</label><input
                                                        class="form-control" name="voip_caller_id"
                                                        placeholder="021... یا داخلی"></div>
                                                <div><label class="form-label">نام تقویم</label><input
                                                        class="form-control" name="calendar_name"
                                                        placeholder="Sales followups"></div>
                                                <div><label class="form-label">پوشه Drive</label><input
                                                        class="form-control" name="drive_folder"
                                                        placeholder="CRM/Customers"></div>
                                            </div>
                                            <button class="btn btn-primary mt-3" type="submit">ساخت
                                                connection</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Sync سریع</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="mb-4" method="POST"
                                            action="{{ route('crm.integrations.calendar.sync-followup') }}">
                                            @csrf
                                            <div class="mb-3"><label class="form-label">Connection
                                                    تقویم</label><select class="form-select" name="connection_id"
                                                    required>
                                                    @foreach ($connections->where('type', 'calendar')->where('is_active', true) as $connection)
                                                        <option value="{{ $connection->id }}">
                                                            {{ $connection->title }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="mb-3"><label class="form-label">پیگیری</label><select
                                                    class="form-select" name="followup_id" required>
                                                    @foreach ($followups as $followup)
                                                        <option value="{{ $followup->id }}">#{{ $followup->id }} -
                                                            {{ $followup->title }}</option>
                                                    @endforeach
                                                </select></div>
                                            <button class="btn btn-outline-primary w-100" type="submit">ثبت رویداد
                                                تقویم</button>
                                        </form>
                                        <form class="mb-4" method="POST"
                                            action="{{ route('crm.integrations.voip.click-to-call') }}">
                                            @csrf
                                            <div class="mb-3"><label class="form-label">Connection
                                                    VoIP</label><select class="form-select" name="connection_id"
                                                    required>
                                                    @foreach ($connections->where('type', 'voip')->where('is_active', true) as $connection)
                                                        <option value="{{ $connection->id }}">
                                                            {{ $connection->title }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="row g-2">
                                                <div class="col-md-6"><input class="form-control" name="phone_number"
                                                        required placeholder="شماره تماس"></div>
                                                <div class="col-md-6"><input class="form-control" name="contact_name"
                                                        placeholder="نام مخاطب"></div>
                                            </div>
                                            <input class="form-control mt-2" name="subject"
                                                placeholder="موضوع تماس خروجی">
                                            <button class="btn btn-outline-info w-100 mt-2"
                                                type="submit">Click-to-call</button>
                                        </form>
                                        <form method="POST" action="{{ route('crm.integrations.drive.link') }}">
                                            @csrf
                                            <div class="mb-3"><label class="form-label">Connection
                                                    Drive</label><select class="form-select" name="connection_id"
                                                    required>
                                                    @foreach ($connections->where('type', 'drive')->where('is_active', true) as $connection)
                                                        <option value="{{ $connection->id }}">
                                                            {{ $connection->title }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="row g-2">
                                                <div class="col-md-5"><select class="form-select" name="target_type"
                                                        required>
                                                        <option value="customer">مشتری</option>
                                                        <option value="followup">پیگیری</option>
                                                        <option value="call">تماس</option>
                                                    </select></div>
                                                <div class="col-md-7"><input class="form-control" name="target_id"
                                                        type="number" min="1" required
                                                        placeholder="شناسه رکورد"></div>
                                            </div>
                                            <input class="form-control mt-2" name="title" required
                                                placeholder="عنوان فایل">
                                            <input class="form-control mt-2 ltr-chip" name="external_url" required
                                                placeholder="https://drive.example/file/...">
                                            <textarea class="form-control mt-2" name="note" rows="2" placeholder="توضیح"></textarea>
                                            <button class="btn btn-outline-success w-100 mt-2" type="submit">ثبت لینک
                                                فایل</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Connectionها</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>عنوان</th>
                                            <th>نوع</th>
                                            <th>Provider</th>
                                            <th>Adapter</th>
                                            <th>Webhook</th>
                                            <th>آخرین sync</th>
                                            <th>Log</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($connections as $connection)
                                            <tr>
                                                <td>{{ $connection->title }}<div class="text-muted small ltr-chip">
                                                        {{ $connection->endpoint_url ?: '-' }}</div>
                                                </td>
                                                <td>{{ $connection->typeText() }}</td>
                                                <td>{{ $connection->providerText() }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-info">{{ $connection->endpoint_url ? 'live endpoint' : 'queued' }}</span>
                                                    @if (!empty(($connection->credentials ?: [])['outbound_auth_token']))
                                                        <span class="badge bg-label-success">token</span>
                                                    @endif
                                                </td>
                                                <td><code
                                                        class="ltr-chip">{{ route('crm.integrations.voip.webhook', $connection) }}</code>
                                                </td>
                                                <td>{{ optional($connection->last_synced_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                                <td>{{ number_format($connection->logs_count) }}</td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('crm.integrations.toggle', $connection) }}">
                                                        @csrf @method('PATCH')
                                                        <button
                                                            class="btn btn-sm {{ $connection->is_active ? 'btn-label-success' : 'btn-label-secondary' }}"
                                                            type="submit">{{ $connection->is_active ? 'فعال' : 'غیرفعال' }}</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">connection ثبت نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Sync log</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>زمان</th>
                                            <th>Connection</th>
                                            <th>عملیات</th>
                                            <th>جهت</th>
                                            <th>رکورد</th>
                                            <th>وضعیت</th>
                                            <th>پیام</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                                <td>{{ optional($log->connection)->title ?: '-' }}</td>
                                                <td>{{ $log->operation }}<div class="text-muted small ltr-chip">
                                                        {{ $log->external_id }}</div>
                                                </td>
                                                <td>{{ $log->direction }}</td>
                                                <td>{{ class_basename($log->syncable_type ?: '') }}
                                                    #{{ $log->syncable_id ?: '-' }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $log->status === 'synced' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ $log->statusText() }}</span>
                                                </td>
                                                <td class="text-wrap">{{ $log->message }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">هنوز sync log ثبت
                                                    نشده است.</td>
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
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
</body>

</html>
