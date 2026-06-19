{{--
    سلکتور آبشاری حساب (سبک سپیدار): حساب کل ← حساب معین ← حساب تفصیل
    ورودی‌ها:
      $accounts          مجموعهٔ تخت حساب‌ها (همان accountingAccounts کنترلر)
      $selectedAccountId شناسهٔ حسابِ ذخیره‌شده/قبلی برای پیش‌انتخاب (می‌تواند خالی باشد)
    حسابِ نهاییِ ارسالی در input مخفی account_id[] قرار می‌گیرد و خصوصی‌ترین سطح انتخاب‌شده را آینه می‌کند.
--}}
@php
    $selectedAccountId = $selectedAccountId ?? '';
@endphp
<div class="account-cascader" data-selected="{{ $selectedAccountId }}">
    <select class="form-select form-select-sm select2-basic account-cascader-kol mb-1" data-role="kol"
        aria-label="حساب کل">
        <option value="">حساب کل</option>
        @foreach ($accounts as $account)
            @if ((int) ($account->parent_id ?? 0) === 0)
                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
            @endif
        @endforeach
    </select>
    <select class="form-select form-select-sm select2-basic account-cascader-moein mb-1" data-role="moein"
        aria-label="حساب معین" disabled>
        <option value="">حساب معین</option>
    </select>
    <select class="form-select form-select-sm select2-basic account-cascader-tafsil" data-role="tafsil"
        aria-label="حساب تفصیل" disabled>
        <option value="">حساب تفصیل</option>
    </select>
    <input type="hidden" name="account_id[]" class="account-cascader-id" value="{{ $selectedAccountId }}">
</div>
