<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    @include('sections.head')
    <title>راهنمای مرحله‌ای راه‌اندازی - دکان دارمینو</title>
    <style>
        .setup-guide-page {
            direction: rtl;
        }

        .setup-guide-page .sg-track-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #e7e8f2;
            border-radius: 12px;
            padding: 0.7rem 0.9rem;
            margin-bottom: 0.6rem;
            color: #384551;
            text-decoration: none;
            background: #fff;
            transition: all 0.2s ease;
        }

        .setup-guide-page .sg-track-link:hover {
            border-color: #cbcde8;
            background: #f8f9ff;
        }

        .setup-guide-page .sg-track-link.active {
            border-color: #543c92;
            background: #f3f1fb;
            color: #3d2b7c;
            font-weight: 600;
        }

        .setup-guide-page .sg-step-card {
            border: 1px solid #ececf4;
            border-radius: 14px;
            padding: 1rem;
            background: #fff;
        }

        .setup-guide-page .sg-step-card.current {
            border-color: #543c92;
            box-shadow: 0 6px 22px rgba(84, 60, 146, 0.12);
        }

        .setup-guide-page .sg-step-meta {
            font-size: 0.83rem;
            color: #7a8197;
            margin-bottom: 0.35rem;
        }

        .setup-guide-page .sg-box {
            border: 1px dashed #d7d9ea;
            border-radius: 10px;
            padding: 0.7rem;
            margin-bottom: 0.75rem;
            background: #fafbff;
        }

        .setup-guide-page .sg-box h6 {
            font-size: 0.88rem;
            margin-bottom: 0.4rem;
            color: #2f3750;
        }

        .setup-guide-page .sg-box ul {
            margin: 0;
            padding-right: 1rem;
        }
    </style>
</head>

<body>
    @php
        $steps = (array) ($selectedTrack['steps'] ?? []);
    @endphp

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y setup-guide-page">
                        <h4 class="py-3 mb-2">
                            <span class="text-muted fw-light">راهنمای راه‌اندازی /</span>
                            مسیر مرحله‌ای پنل
                        </h4>

                        <div class="row g-4">
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="card-header pb-2">
                                        <h5 class="mb-1">ماژول‌های راه‌اندازی</h5>
                                        <small class="text-muted">مسیر موردنظر را انتخاب کنید</small>
                                    </div>
                                    <div class="card-body pt-2">
                                        @foreach ($tracks as $trackKey => $track)
                                            <a class="sg-track-link {{ $selectedTrackKey === $trackKey ? 'active' : '' }}"
                                                href="{{ route('setup-guide.index', ['track' => $trackKey]) }}">
                                                <span>
                                                    <x-ui.icon name="{{ $track['icon'] ?? 'map' }}" class="me-1" />
                                                    {{ $track['title'] ?? $trackKey }}
                                                </span>
                                                <span class="badge bg-label-secondary">{{ count($track['steps'] ?? []) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-9">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h4 class="mb-1">
                                            <x-ui.icon name="{{ $selectedTrack['icon'] ?? 'map' }}" class="me-1" />
                                            مسیر {{ $selectedTrack['title'] ?? 'راهنما' }}
                                        </h4>
                                        <p class="text-muted mb-0">{{ $selectedTrack['intro'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    @foreach ($steps as $index => $step)
                                        <div class="col-12">
                                            <article class="sg-step-card {{ $index === 0 ? 'current' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <div class="sg-step-meta">مرحله {{ $index + 1 }}</div>
                                                        <h5 class="mb-0">{{ $step['title'] ?? '-' }}</h5>
                                                    </div>
                                                    <span
                                                        class="badge {{ $index === 0 ? 'bg-label-primary' : 'bg-label-secondary' }}">
                                                        {{ $index === 0 ? 'گام پیشنهادی فعلی' : 'گام بعدی' }}
                                                    </span>
                                                </div>

                                                <div class="sg-box">
                                                    <h6>هدف مرحله</h6>
                                                    <p class="mb-0">{{ $step['goal'] ?? '-' }}</p>
                                                </div>

                                                @if (!empty($step['access_path']))
                                                    <div class="sg-box">
                                                        <h6>مسیر دسترسی</h6>
                                                        <p class="mb-0">{{ $step['access_path'] }}</p>
                                                    </div>
                                                @endif

                                                <div class="sg-box">
                                                    <h6>کارهایی که باید انجام شود</h6>
                                                    <ul>
                                                        @foreach ((array) ($step['checklist'] ?? []) as $task)
                                                            <li>{{ $task }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <div class="sg-box">
                                                    <h6>پیش‌نیازها</h6>
                                                    <ul>
                                                        @foreach ((array) ($step['prerequisites'] ?? []) as $requirement)
                                                            <li>{{ $requirement }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                @if (!empty($step['tips']))
                                                    <div class="sg-box">
                                                        <h6>نکات</h6>
                                                        <ul>
                                                            @foreach ((array) $step['tips'] as $tip)
                                                                <li>{{ $tip }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <div class="sg-box">
                                                    <h6>خروجی مورد انتظار</h6>
                                                    <p class="mb-0">{{ $step['expected_output'] ?? '-' }}</p>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="text-muted">
                                                        <strong>مرحله بعدی:</strong> {{ $step['next_step'] ?? '-' }}
                                                    </div>
                                                    @if (!empty($step['action']['route']))
                                                        <a class="btn btn-sm btn-primary"
                                                            href="{{ route($step['action']['route']) }}">
                                                            {{ $step['action']['label'] ?? 'اقدام مستقیم' }}
                                                            <x-ui.icon name="arrow-left" class="ms-1" />
                                                        </a>
                                                    @endif
                                                </div>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('sections.script')
</body>

</html>
