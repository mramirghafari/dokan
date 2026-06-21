<?php
    $emp = $employee ?? null;
    $val = function ($field, $default = '') use ($emp) {
        return old($field, $emp ? ($emp->{$field} ?? '') : $default);
    };
?>
<div class="card-body">
    <h6 class="text-primary mb-3">اطلاعات هویتی</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
            <input class="form-control" name="name" value="{{ $val('name') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">نام پدر</label>
            <input class="form-control" name="father_name" value="{{ $val('father_name') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">کد ملی</label>
            <input class="form-control" name="national_code" value="{{ $val('national_code') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">شماره شناسنامه</label>
            <input class="form-control" name="personalID" value="{{ $val('personalID') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">شماره موبایل</label>
            <input class="form-control" name="mobile" value="{{ $val('mobile') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">وضعیت تاهل</label>
            <select class="form-select" name="marital_status">
                <option value="">-</option>
                @foreach (\App\Models\Employee::MARITAL_STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($val('marital_status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">تعداد اولاد (مشمول حق اولاد)</label>
            <input class="form-control" name="children_count" type="number" min="0" max="30"
                value="{{ $val('children_count', 0) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">وضعیت نظام وظیفه</label>
            <select class="form-select" name="military_status">
                <option value="">-</option>
                @foreach (\App\Models\Employee::MILITARY_STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($val('military_status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <h6 class="text-primary mb-3">اطلاعات استخدامی</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">واحد سازمانی <span class="text-danger">*</span></label>
            <select class="form-select" name="organization_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}" @selected((int) $val('organization_id') === (int) $organization->id)>
                        {{ $organization->name ?? $organization->title ?? ('شرکت ' . $organization->id) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">کد پرسنلی</label>
            <input class="form-control" name="personnel_code" value="{{ $val('personnel_code') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">سمت / عنوان شغلی</label>
            <input class="form-control" name="job_title" value="{{ $val('job_title') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">نوع همکاری</label>
            <select class="form-select" name="employment_type">
                <option value="">-</option>
                @foreach (\App\Models\Employee::EMPLOYMENT_TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($val('employment_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">تاریخ استخدام (میلادی)</label>
            <input class="form-control" name="hire_date_en" type="date" value="{{ $val('hire_date_en') }}">
            @if ($emp && $emp->hire_date_fa)
                <small class="text-muted">شمسی: {{ $emp->hire_date_fa }}</small>
            @endif
        </div>
        <div class="col-md-4">
            <label class="form-label">شماره بیمه</label>
            <input class="form-control" name="insurance_number" value="{{ $val('insurance_number') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">وضعیت اشتغال</label>
            <select class="form-select" name="employment_status">
                @foreach (\App\Models\Employee::EMPLOYMENT_STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($val('employment_status', 'active') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <h6 class="text-primary mb-3">اطلاعات بانکی</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">نام بانک</label>
            <input class="form-control" name="bank_name" value="{{ $val('bank_name') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">شماره حساب</label>
            <input class="form-control" name="bank_account" value="{{ $val('bank_account') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">شماره شبا</label>
            <input class="form-control" name="sheba" value="{{ $val('sheba') }}">
        </div>
        <div class="col-12">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="isActive"
                    @checked($emp ? $emp->isActive : true)>
                <span class="form-check-label">فعال</span>
            </label>
        </div>
    </div>
</div>
