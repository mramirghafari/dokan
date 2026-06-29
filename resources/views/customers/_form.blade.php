@php
    $isEdit = isset($customer);
    $customerModel = $customer ?? null;
    $fieldRequired = fn(string $field) => $formFields->isRequired('customer', $field);
    $fieldVisible = fn(string $field) => $formFields->isVisible('customer', $field);
    $reqMark = fn(string $field) => $fieldRequired($field) ? '<span class="text-danger">*</span>' : '';
@endphp

<div class="mb-4">
    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">اطلاعات اصلی</h6>
    <div class="row g-3">
        @if ($fieldVisible('name'))
            <div class="col-md-4">
                <label class="form-label" for="fullname">نام کامل مشتری {!! $reqMark('name') !!}</label>
                <input class="form-control" id="fullname" placeholder="نام و نام خانوادگی" name="name" type="text"
                    value="{{ old('name', $customerModel?->name) }}" @if ($fieldRequired('name')) required @endif />
            </div>
        @endif
        @if ($fieldVisible('tablo'))
            <div class="col-md-4">
                <label class="form-label" for="tablo">تابلو مشتری {!! $reqMark('tablo') !!}</label>
                <input class="form-control" id="tablo" placeholder="تابلو مشتری" name="tablo" type="text"
                    value="{{ old('tablo', $customerModel?->tablo) }}" @if ($fieldRequired('tablo')) required @endif />
            </div>
        @endif
        @if ($fieldVisible('customer_code'))
            <div class="col-md-4">
                <label class="form-label" for="customer_code">کد مشتری {!! $reqMark('customer_code') !!}</label>
                <input class="form-control" id="customer_code" placeholder="کد مشتری" name="customer_code" type="text"
                    value="{{ old('customer_code', $customerModel?->customer_code) }}"
                    @if ($fieldRequired('customer_code')) required @endif />
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">اطلاعات تماس</h6>
    <div class="row g-3">
        @if ($fieldVisible('phone'))
            <div class="col-md-4">
                <label class="form-label" for="phone">شماره تلفن {!! $reqMark('phone') !!}</label>
                <input class="form-control" id="phone" placeholder="مثال: 02112345678" name="phone" type="text"
                    value="{{ old('phone', $customerModel?->phone) }}" @if ($fieldRequired('phone')) required @endif />
            </div>
        @endif
        @if ($fieldVisible('mobile'))
            <div class="col-md-4">
                <label class="form-label" for="mobile">شماره موبایل {!! $reqMark('mobile') !!}</label>
                <input class="form-control" id="mobile" placeholder="مثال: 09121234567" name="mobile" type="text"
                    value="{{ old('mobile', $customerModel?->mobile) }}" @if ($fieldRequired('mobile')) required @endif />
            </div>
        @endif
        @if ($fieldVisible('national_id'))
            <div class="col-md-4">
                <label class="form-label" for="national_id">کد ملی {!! $reqMark('national_id') !!}</label>
                <input class="form-control" id="national_id" placeholder="۱۰ رقم" name="national_id" type="text"
                    value="{{ old('national_id', $customerModel?->national_id) }}"
                    @if ($fieldRequired('national_id')) required @endif />
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">طبقه‌بندی</h6>
    <div class="row g-3">
        @if ($fieldVisible('mapcode'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="mapcode">مپ کد {!! $reqMark('mapcode') !!}</label>
                <input class="form-control" id="mapcode" placeholder="مپ کد مشتری" name="mapcode" type="text"
                    value="{{ old('mapcode', $customerModel?->mapcode) }}" @if ($fieldRequired('mapcode')) required @endif />
            </div>
        @endif
        @if ($fieldVisible('customer_group_id'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="customer_group_id">گروه مشتری {!! $reqMark('customer_group_id') !!}</label>
                <select class="select2 form-select w-100" id="customer_group_id" name="customer_group_id"
                    @if ($fieldRequired('customer_group_id')) required @endif>
                    <option value="0">انتخاب کنید</option>
                    @foreach ($customerGroups as $segment)
                        <option value="{{ $segment->id }}"
                            @if (old('customer_group_id', $customerModel?->customer_group_id) == $segment->id) selected @endif>
                            {{ $segment->title }}</option>
                    @endforeach
                </select>
                @if ($customerGroups->isEmpty())
                    <small class="text-warning d-block mt-1">گروهی تعریف نشده —
                        <a href="{{ route('customer-groups.index') }}">ایجاد گروه مشتری</a></small>
                @endif
            </div>
        @endif
        @if ($fieldVisible('sales_channel_id'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="sales_channel_id">کانال فروش {!! $reqMark('sales_channel_id') !!}</label>
                <select class="select2 form-select w-100" id="sales_channel_id" name="sales_channel_id"
                    @if ($fieldRequired('sales_channel_id')) required @endif>
                    <option value="0">انتخاب کنید</option>
                    @foreach ($salesChannels as $segment)
                        <option value="{{ $segment->id }}"
                            @if (old('sales_channel_id', $customerModel?->sales_channel_id) == $segment->id) selected @endif>
                            {{ $segment->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($fieldVisible('customer_status_id'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="customer_status_id">وضعیت مشتری {!! $reqMark('customer_status_id') !!}</label>
                <select class="select2 form-select w-100" id="customer_status_id" name="customer_status_id"
                    @if ($fieldRequired('customer_status_id')) required @endif>
                    <option value="0">انتخاب کنید</option>
                    @foreach ($customerStatuses as $segment)
                        <option value="{{ $segment->id }}"
                            @if (old('customer_status_id', $customerModel?->customer_status_id) == $segment->id || (!$isEdit && !old('customer_status_id') && $segment->is_default)) selected @endif>
                            {{ $segment->title }}
                            @if ($segment->title === 'فعال')
                                (فعال)
                            @elseif ($segment->title === 'غیرفعال')
                                (غیرفعال)
                            @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">گزینه‌های پیش‌فرض: فعال، غیرفعال، مسدود</small>
            </div>
        @endif

        @if ($usesAreaWorkflow && $fieldVisible('region_id'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="region_id">منطقه مشتری
                    @if ($requiresAreaWorkflow || $fieldRequired('region_id'))
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select class="select2 form-select w-100" id="region_id" name="region_id"
                    @if ($requiresAreaWorkflow || $fieldRequired('region_id')) required @endif>
                    <option value="0">انتخاب کنید</option>
                    @foreach ($Regions as $region)
                        <option value="{{ $region->id }}"
                            @if (old('region_id', $isEdit ? ($Cur_Region->id ?? $customerModel?->region_id) : null) == $region->id) selected @endif>
                            {{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
        @elseif (!$usesAreaWorkflow)
            <input name="region_id" type="hidden" value="0">
        @endif

        @if ($usesRouteWorkflow && $fieldVisible('area'))
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="areas">انتخاب مسیر
                    @if ($requiresRouteWorkflow || $fieldRequired('area'))
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select class="select2 form-select w-100" id="areas" name="area"
                    @if ($requiresRouteWorkflow || $fieldRequired('area')) required @endif>
                    <option value="0">انتخاب کنید</option>
                    @if ($isEdit && isset($This_areas))
                        @foreach ($This_areas as $t_areas)
                            <option value="{{ $t_areas->id }}"
                                @if (old('area', $customerModel?->area) == $t_areas->id) selected @endif>
                                {{ $t_areas->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @elseif (!$usesRouteWorkflow)
            <input name="area" type="hidden" value="0">
        @endif
    </div>
</div>

@if ($isEdit)
    <div class="mb-4">
        <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">محدودیت‌های مالی</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="max_purchase_amount">سقف مبلغ خرید (ریال)</label>
                <input class="form-control seprator" id="max_purchase_amount" name="max_purchase_amount" type="text"
                    value="{{ old('max_purchase_amount', $customerModel?->max_purchase_amount ? number_format((float) $customerModel->max_purchase_amount) : '') }}"
                    placeholder="مثلاً ۵۰,۰۰۰,۰۰۰" />
            </div>
            <div class="col-md-6">
                <label class="form-label" for="max_discount_amount">سقف مبلغ تخفیف (ریال)</label>
                <input class="form-control seprator" id="max_discount_amount" name="max_discount_amount" type="text"
                    value="{{ old('max_discount_amount', $customerModel?->max_discount_amount ? number_format((float) $customerModel->max_discount_amount) : '') }}"
                    placeholder="مثلاً ۱,۰۰۰,۰۰۰" />
            </div>
        </div>
    </div>
@endif

<div class="mb-0">
    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">{{ $isEdit ? 'آدرس' : 'آدرس و موقعیت' }}</h6>
    <div class="row g-3">
        @if ($fieldVisible('address'))
            <div class="col-md-6">
                <label class="form-label" for="address">آدرس فروشگاه {!! $reqMark('address') !!}</label>
                <textarea class="form-control" id="address" name="address" rows="3"
                    @if ($fieldRequired('address')) required @endif
                    placeholder="آدرس کامل فروشگاه مشتری">{{ old('address', $customerModel?->address) }}</textarea>
            </div>
        @endif
        @if ($fieldVisible('store_address'))
            <div class="col-md-6">
                <label class="form-label" for="store_address">آدرس انبار {!! $reqMark('store_address') !!}</label>
                <textarea class="form-control" id="store_address" name="store_address" rows="3"
                    @if ($fieldRequired('store_address')) required @endif
                    placeholder="در صورت متفاوت بودن با آدرس فروشگاه">{{ old('store_address', $customerModel?->store_address) }}</textarea>
                @if (!$fieldRequired('store_address'))
                    <small class="text-muted">اختیاری — در صورت یکسان بودن با فروشگاه خالی بگذارید.</small>
                @endif
            </div>
        @endif

        @if (!$isEdit && ($fieldVisible('shop_lat') || $fieldVisible('store_lat')))
            @if ($fieldVisible('shop_lat'))
                <div class="col-12">
                    <label class="form-label" for="map_get">لوکیشن فروشگاه {!! $reqMark('shop_lat') !!}</label>
                    <small class="text-muted d-block mb-2">نشانگر را روی محل فروشگاه بکشید.</small>
                    <div id="map_get" class="customer-form-map rounded border"></div>
                    <input type="hidden" id="shop_lat" name="shop_lat" value="{{ old('shop_lat') }}" />
                    <input type="hidden" id="shop_lng" name="shop_lng" value="{{ old('shop_lng') }}" />
                </div>
            @endif
            @if ($fieldVisible('store_lat'))
                <div class="col-12">
                    <label class="form-label" for="map_get_store">لوکیشن انبار {!! $reqMark('store_lat') !!}</label>
                    <small class="text-muted d-block mb-2">در صورت نبود انبار جداگانه، همان موقعیت فروشگاه را انتخاب
                        کنید.</small>
                    <div id="map_get_store" class="customer-form-map rounded border"></div>
                    <input type="hidden" id="store_lat" name="store_lat" value="{{ old('store_lat') }}" />
                    <input type="hidden" id="store_lng" name="store_lng" value="{{ old('store_lng') }}" />
                </div>
            @endif
        @endif
    </div>
</div>
