<!DOCTYPE html>
<html dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>فیش حقوقی - {{ $payrollRun->number }}</title>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <style>
        body {
            background: #f4f5fb;
            padding: 1.5rem;
            font-family: Tahoma, sans-serif;
        }

        .payslip {
            background: #fff;
            border: 1px solid #d9dbe9;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin: 0 auto 1.25rem;
            max-width: 820px;
            page-break-after: always;
        }

        .payslip h5 {
            margin: 0;
        }

        .payslip table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.75rem;
        }

        .payslip th,
        .payslip td {
            border: 1px solid #e3e4ef;
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }

        .payslip th {
            background: #f6f6fb;
            text-align: right;
        }

        .totals td {
            font-weight: 700;
            background: #fafbff;
        }

        .net {
            background: #eafaf0 !important;
            color: #117a44;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .payslip {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="no-print text-center mb-3">
        <button class="btn btn-primary" onclick="window.print()">چاپ فیش‌ها</button>
        <a class="btn btn-outline-secondary" href="{{ route('Accounting.payroll') }}">بازگشت</a>
    </div>

    @forelse ($payrollRun->items as $item)
        <?php
            $earnings = $item->components->where('component_type', 'earning');
            $deductions = $item->components->where('component_type', 'deduction');
        ?>
        <div class="payslip">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                <div>
                    <h5>فیش حقوقی</h5>
                    <small class="text-muted">دورهٔ {{ $payrollRun->period_year }}/{{ $payrollRun->period_month }}
                        - لیست {{ $payrollRun->number }}</small>
                </div>
                <div class="text-start">
                    <div><strong>{{ optional($item->employee)->name }}</strong></div>
                    <small class="text-muted">
                        کد پرسنلی: {{ optional($item->employee)->personnel_code ?: '-' }}
                        | کد ملی: {{ optional($item->employee)->national_code ?: '-' }}
                    </small><br>
                    <small class="text-muted">
                        سمت: {{ optional($item->employee)->job_title ?: (optional($item->contract)->job_title ?: '-') }}
                        | شماره بیمه: {{ optional($item->employee)->insurance_number ?: '-' }}
                    </small>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <small class="text-muted">روز کارکرد: {{ number_format((float) $item->work_days, 2) }}</small>
                </div>
                <div class="col-6 text-start">
                    <small class="text-muted">ساعت اضافه‌کاری:
                        {{ number_format((float) $item->overtime_hours, 2) }}</small>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">مزایا (پرداختی‌ها)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($earnings as $component)
                                <tr>
                                    <td>{{ $component->title }}</td>
                                    <td class="text-start">{{ number_format((float) $component->amount) }}</td>
                                </tr>
                            @endforeach
                            <tr class="totals">
                                <td>جمع مزایا (ناخالص)</td>
                                <td class="text-start">{{ number_format((float) $item->gross_salary) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">کسورات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deductions as $component)
                                <tr>
                                    <td>{{ $component->title }}</td>
                                    <td class="text-start">{{ number_format((float) $component->amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">کسوراتی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                            <tr class="totals">
                                <td>جمع کسورات</td>
                                <td class="text-start">
                                    {{ number_format((float) ($item->gross_salary - $item->net_pay_amount)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <table>
                <tbody>
                    <tr class="totals net">
                        <td>خالص پرداختی</td>
                        <td class="text-start">{{ number_format((float) $item->net_pay_amount) }} ریال</td>
                    </tr>
                    <tr>
                        <td>بیمهٔ سهم کارفرما (هزینهٔ کارفرما، خارج از فیش)</td>
                        <td class="text-start">{{ number_format((float) $item->employer_insurance_amount) }}</td>
                    </tr>
                </tbody>
            </table>

            @if ($item->description)
                <p class="mt-2 mb-0"><small class="text-muted">شرح: {{ $item->description }}</small></p>
            @endif
        </div>
    @empty
        <div class="payslip text-center text-muted">ردیفی برای این لیست حقوق ثبت نشده است.</div>
    @endforelse
</body>

</html>
