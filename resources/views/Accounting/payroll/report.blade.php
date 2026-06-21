<!DOCTYPE html>
<html dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>لیست حقوق، بیمه و مالیات - {{ $payrollRun->number }}</title>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <style>
        body {
            background: #f4f5fb;
            padding: 1.5rem;
            font-family: Tahoma, sans-serif;
        }

        .sheet {
            background: #fff;
            border: 1px solid #d9dbe9;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin: 0 auto 1.25rem;
            max-width: 1100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        th,
        td {
            border: 1px solid #e3e4ef;
            padding: 0.4rem 0.55rem;
            font-size: 0.82rem;
            text-align: center;
        }

        th {
            background: #f6f6fb;
        }

        tfoot td {
            font-weight: 700;
            background: #fafbff;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .sheet {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="no-print text-center mb-3">
        <button class="btn btn-primary" onclick="window.print()">چاپ گزارش</button>
        <a class="btn btn-outline-secondary" href="{{ route('Accounting.payroll') }}">بازگشت</a>
    </div>

    <?php
        $items = $payrollRun->items;
        $sum = function ($field) use ($items) {
            return (float) $items->sum($field);
        };
    ?>

    <div class="sheet">
        <h5 class="mb-1">لیست حقوق ماهانه</h5>
        <small class="text-muted">دورهٔ {{ $payrollRun->period_year }}/{{ $payrollRun->period_month }} - لیست
            {{ $payrollRun->number }}
            @if ($payrollRun->accountingVoucher)
                - سند حسابداری {{ $payrollRun->accountingVoucher->voucher_number }}
            @endif
        </small>
        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>پرسنل</th>
                    <th>روز کارکرد</th>
                    <th>حقوق پایه</th>
                    <th>مزایا</th>
                    <th>ناخالص</th>
                    <th>بیمه سهم کارمند</th>
                    <th>مالیات</th>
                    <th>سایر کسورات</th>
                    <th>خالص پرداختی</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ optional($item->employee)->name }}</td>
                        <td>{{ number_format((float) $item->work_days, 1) }}</td>
                        <td>{{ number_format((float) $item->base_salary) }}</td>
                        <td>{{ number_format((float) $item->benefits_amount) }}</td>
                        <td>{{ number_format((float) $item->gross_salary) }}</td>
                        <td>{{ number_format((float) $item->employee_insurance_amount) }}</td>
                        <td>{{ number_format((float) $item->tax_amount) }}</td>
                        <td>{{ number_format((float) ($item->other_deductions_amount + $item->loan_deduction_amount + $item->advance_deduction_amount)) }}
                        </td>
                        <td>{{ number_format((float) $item->net_pay_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">جمع کل</td>
                    <td>{{ number_format($sum('base_salary')) }}</td>
                    <td>{{ number_format($sum('benefits_amount')) }}</td>
                    <td>{{ number_format($sum('gross_salary')) }}</td>
                    <td>{{ number_format($sum('employee_insurance_amount')) }}</td>
                    <td>{{ number_format($sum('tax_amount')) }}</td>
                    <td>{{ number_format($sum('other_deductions_amount') + $sum('loan_deduction_amount') + $sum('advance_deduction_amount')) }}
                    </td>
                    <td>{{ number_format($sum('net_pay_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="sheet">
        <h5 class="mb-1">لیست بیمه (دیسکت تأمین اجتماعی)</h5>
        <small class="text-muted">مبنای کسر حق بیمه دورهٔ {{ $payrollRun->period_year }}/{{ $payrollRun->period_month }}</small>
        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>پرسنل</th>
                    <th>شماره بیمه</th>
                    <th>روز کارکرد</th>
                    <th>دستمزد مشمول بیمه</th>
                    <th>سهم کارمند</th>
                    <th>سهم کارفرما</th>
                    <th>جمع حق بیمه</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ optional($item->employee)->name }}</td>
                        <td>{{ optional($item->employee)->insurance_number ?: '-' }}</td>
                        <td>{{ number_format((float) $item->work_days, 1) }}</td>
                        <td>{{ number_format((float) $item->insurance_subject_amount) }}</td>
                        <td>{{ number_format((float) $item->employee_insurance_amount) }}</td>
                        <td>{{ number_format((float) $item->employer_insurance_amount) }}</td>
                        <td>{{ number_format((float) ($item->employee_insurance_amount + $item->employer_insurance_amount)) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">جمع کل</td>
                    <td>{{ number_format($sum('insurance_subject_amount')) }}</td>
                    <td>{{ number_format($sum('employee_insurance_amount')) }}</td>
                    <td>{{ number_format($sum('employer_insurance_amount')) }}</td>
                    <td>{{ number_format($sum('employee_insurance_amount') + $sum('employer_insurance_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="sheet">
        <h5 class="mb-1">لیست مالیات حقوق</h5>
        <small class="text-muted">دورهٔ {{ $payrollRun->period_year }}/{{ $payrollRun->period_month }}</small>
        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>پرسنل</th>
                    <th>کد ملی</th>
                    <th>درآمد مشمول مالیات</th>
                    <th>مالیات حقوق</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ optional($item->employee)->name }}</td>
                        <td>{{ optional($item->employee)->national_code ?: '-' }}</td>
                        <td>{{ number_format((float) $item->taxable_amount) }}</td>
                        <td>{{ number_format((float) $item->tax_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">جمع کل</td>
                    <td>{{ number_format($sum('taxable_amount')) }}</td>
                    <td>{{ number_format($sum('tax_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
