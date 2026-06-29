@php
    $voucherDateEn = old('voucher_date_en', $today ?? now()->toDateString());
    $voucherDateFa = verta_date($voucherDateEn);
@endphp
<label class="form-label">تاریخ سند</label>
<input type="text" class="form-control voucher-date-jalali" id="voucher_date_fa"
    value="{{ $voucherDateFa }}" data-jdp data-jdp-miladi-input="voucher_date_en" placeholder="تاریخ سند"
    autocomplete="off">
<input type="hidden" name="voucher_date_en" id="voucher_date_en" value="{{ $voucherDateEn }}">
