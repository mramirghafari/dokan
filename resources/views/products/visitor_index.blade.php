<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت سفارش / پیش‌فاکتور - دکان دارمینو</title>
    <meta content="" name="description" />
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/product-neworder.css') }}?v=2" rel="stylesheet" />
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y neworder-page">
                        @php
                            $Task = $Task ?? null;
                            $isAgent = $isAgent ?? false;
                            $productCount = $productCount ?? $products->count();
                            $showProducts = $isAgent || isset($_GET['Customer']);
                            $initialButtonClass = $showProducts ? 'btn-success' : 'btn-info';
                            $initialButtonLabel = $showProducts ? 'ثبت سفارش' : 'ادامه و انتخاب محصول';
                            $featureWarehouseManagement = $featureWarehouseManagement ?? \App\Services\TenantSettings::enabled('feature_warehouse_management');
                            $productDiscountLimits = $productDiscountLimits ?? [];
                        @endphp

                        <div class="card neworder-hero" id="tour-neworder-page-header">
                            <div class="card-body">
                                <div id="tour-neworder-page-title">
                                    <span class="neworder-hero__eyebrow">فروش و سفارش‌گیری</span>
                                    <h4 class="mb-0">
                                        <span class="text-muted fw-light">سفارش‌ها /</span>
                                        ثبت سفارش / پیش‌فاکتور
                                    </h4>
                                    <p class="neworder-hero__subtitle mb-0">
                                        مشتری را انتخاب کنید، محصولات را جستجو و تعداد بزنید، سپس سفارش را ثبت کنید تا به‌صورت پیش‌فاکتور در سیستم ذخیره شود.
                                    </p>
                                </div>
                                <div class="neworder-hero__stats">
                                    <span class="neworder-hero__stat">
                                        <x-ui.icon name="package" />
                                        {{ number_format($productCount) }} محصول فعال
                                    </span>
                                    @if (!$isAgent)
                                        <span class="neworder-hero__stat">
                                            <x-ui.icon name="users" />
                                            {{ number_format($Customers->count()) }} مشتری در دسترس
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form id="addFactor" method="POST" action="{{ route('add_factor_visitor') }}">
                            <input type="hidden" id="visitor_lat" name="visitor_lat" />
                            <input type="hidden" id="visitor_lng" name="visitor_lng" />
                            @csrf
                            @if ($Task != null)
                                <input type="hidden" name="task_id" value="{{ $Task }}" />
                            @endif

                            @if (!$isAgent)
                                <div class="card neworder-customer-card" id="tour-neworder-customer-card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <x-ui.icon name="user-search" class="me-1" />
                                            انتخاب مشتری
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <label class="form-label" for="tour-neworder-customer-select">انتخاب مشتری برای این فاکتور</label>
                                        <select class="form-control select2 customers_list" id="tour-neworder-customer-select"
                                            name="customer" @if (isset($_GET['Customer'])) disabled @endif>
                                            <option value="">انتخاب کنید...</option>
                                            @foreach ($Customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    data-max-purchase="{{ $customer->max_purchase_amount ?? '' }}"
                                                    data-max-discount="{{ $customer->max_discount_amount ?? '' }}"
                                                    @if (isset($_GET['Customer']) && $_GET['Customer'] == $customer->id) selected @endif
                                                    @if (isset($_GET['Customer'])) readonly @endif>
                                                    {{ $customer->name }}
                                                    @if ($customer->tablo)
                                                        / تابلو: {{ $customer->tablo }}
                                                    @endif
                                                    @if ($customer->mobile)
                                                        / موبایل: {{ $customer->mobile }}
                                                    @endif
                                                    @if ($customer->address)
                                                        / آدرس: {{ $customer->address }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text mt-2">
                                            با نام، موبایل، تابلو یا آدرس در لیست جستجو کنید.
                                        </div>
                                        @if (isset($_GET['Customer']))
                                            <input type="hidden" name="customer" value="{{ $_GET['Customer'] }}">
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div id="productValidationAlert"
                                class="alert alert-danger neworder-validation-alert text-center d-none mb-3"
                                role="alert">
                                <x-ui.icon name="alert-circle" class="me-1" />
                                برای ثبت فاکتور حداقل باید یک محصول را انتخاب نمایید
                            </div>

                            <div class="prlist {{ $showProducts ? '' : 'd-none' }}" id="tour-neworder-products-section">
                                <div class="card neworder-search-card" id="tour-neworder-search">
                                    <div class="card-body py-3">
                                        <label class="form-label mb-2" for="searchInput">جستجوی محصول</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">
                                                <x-ui.icon name="search" />
                                            </span>
                                            <input type="text" class="form-control" id="searchInput"
                                                placeholder="{{ $featureWarehouseManagement ? 'نام، کد SKU یا انبار محصول را بنویسید...' : 'نام یا کد SKU محصول را بنویسید...' }}" />
                                        </div>
                                    </div>
                                </div>

                                @if ($products->isEmpty())
                                    <div class="neworder-empty-state" id="tour-neworder-empty">
                                        <div class="neworder-empty-state__icon">
                                            <x-ui.icon name="package-off" />
                                        </div>
                                        <h5>محصول فعالی برای سفارش وجود ندارد</h5>
                                        <p>
                                            ابتدا از بخش «محصولات» کالای فعال با قیمت تعریف‌شده ثبت کنید، سپس به این صفحه برگردید.
                                        </p>
                                    </div>
                                @else
                                    <div class="row neworder-products-grid" id="tour-neworder-grid">
                                        @foreach ($products as $product)
                                            @php
                                                $qtyMode = $product->resolveOrderQuantityMode();
                                                $productLimits = $productDiscountLimits[$product->id] ?? [];
                                            @endphp
                                            <div class="col-6 col-md-4 col-lg-3 pr_item" data-product-id="{{ $product->id }}">
                                                <div class="neworder-product-card"
                                                    data-pack-items="{{ max(1, (int) $product->pack_items) }}"
                                                    @if ($loop->first) id="tour-neworder-product-card" @endif>
                                                    <div class="neworder-product-card__media @if ($product->photo == null) neworder-product-card__media--placeholder @endif">
                                                        @if ($product->photo == null)
                                                            <x-ui.icon name="photo" />
                                                        @else
                                                            <img alt="{{ $product->title }}"
                                                                src="{{ asset("/storage/uploads/$product->photo") }}" />
                                                        @endif
                                                    </div>

                                                    <div class="neworder-product-card__body">
                                                        <h5 class="neworder-product-card__title">
                                                            {{ $product->title }}
                                                            @if ($product->display_name)
                                                                <span class="d-block text-muted fw-normal small mt-1">{{ $product->display_name }}</span>
                                                            @endif
                                                        </h5>
                                                        <div class="neworder-product-card__meta">
                                                            <div class="neworder-product-card__meta-row">
                                                                <x-ui.icon name="barcode" />
                                                                <span>کد محصول: <strong>{{ $product->sku }}</strong></span>
                                                            </div>
                                                            @if ($featureWarehouseManagement)
                                                            <div class="neworder-product-card__meta-row">
                                                                <x-ui.icon name="building-warehouse" />
                                                                <span>
                                                                    انبار:
                                                                    <strong>
                                                                        @if (is_array(json_decode($product->store_id)))
                                                                            @foreach (json_decode($product->store_id) as $storeid)
                                                                                <?php $Store = DB::table('stores')->where('id', $storeid)->first();
                                                                                echo $Store->title;
                                                                                ?>
                                                                            @endforeach
                                                                        @endif
                                                                    </strong>
                                                                </span>
                                                            </div>
                                                            @endif
                                                            <div class="neworder-product-card__meta-row">
                                                                <x-ui.icon name="box" />
                                                                <span>تعداد در {{ $product->pr_sub_unit }}: <strong>{{ $product->pack_items }}</strong></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if ($product->set_price == 0)
                                                        <div class="neworder-product-card__price">
                                                            <span class="neworder-product-card__price-label">قیمت واحد</span>
                                                            <span class="pricepr">{{ number_format(intval($product->price)) }} ریال</span>
                                                        </div>
                                                    @endif

                                                    <div class="neworder-product-card__controls">
                                                        @if ($qtyMode === 'secondary_unit' || $qtyMode === 'both')
                                                            <div class="neworder-control-group"
                                                                @if ($loop->first) id="tour-neworder-quantity" @endif>
                                                                <span class="neworder-control-group__label">{{ $product->pr_sub_unit ?: 'واحد فرعی' }}</span>
                                                                <div class="d-flex counterbox">
                                                                    <span class="col-2 minus">-</span>
                                                                    <input type="number" class="inputcount col-8" min="0"
                                                                        max="1000" value="0"
                                                                        name="pack_{{ $product->id }}" />
                                                                    <span class="col-2 plus">+</span>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($qtyMode === 'main_unit' || $qtyMode === 'both')
                                                            <div class="neworder-control-group"
                                                                @if ($loop->first && $qtyMode !== 'secondary_unit') id="tour-neworder-quantity" @endif>
                                                                <span class="neworder-control-group__label">{{ $product->pr_unit ?: 'واحد اصلی' }}</span>
                                                                <div class="d-flex counterbox">
                                                                    <span class="col-2 minus">-</span>
                                                                    <input type="number" class="inputcount col-8" min="0"
                                                                        max="1000" value="0"
                                                                        name="item_{{ $product->id }}" />
                                                                    <span class="col-2 plus">+</span>
                                                                </div>
                                                            </div>
                                                        @elseif ($qtyMode === 'none')
                                                            <div class="neworder-control-group">
                                                                <span class="neworder-control-group__label">تک‌فروشی</span>
                                                                <div class="form-check form-switch mt-1">
                                                                    <input class="form-check-input fixed-qty-toggle inputcount"
                                                                        type="checkbox"
                                                                        id="fixed_qty_{{ $product->id }}"
                                                                        data-product-id="{{ $product->id }}" />
                                                                    <label class="form-check-label" for="fixed_qty_{{ $product->id }}">
                                                                        @if ($product->display_name)
                                                                            {{ $product->display_name }}
                                                                        @else
                                                                            افزودن به سفارش (تعداد ۱)
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                                <input type="hidden" name="item_{{ $product->id }}"
                                                                    id="item_{{ $product->id }}" value="0"
                                                                    class="fixed-qty-hidden" />
                                                            </div>
                                                        @endif

                                                        @if ($product->set_price == 1)
                                                            <div class="neworder-control-group neworder-product-card__price-input"
                                                                @if ($loop->first) id="tour-neworder-price" @endif>
                                                                <span class="neworder-control-group__label">
                                                                    قیمت هر {{ $product->pr_unit }} (ریال)
                                                                </span>
                                                                <input type="text" class="form-control price"
                                                                    name="price_{{ $product->id }}"
                                                                    value="{{ intval($product->price) > 0 ? number_format(intval($product->price)) : '0' }}" />
                                                            </div>
                                                        @endif

                                                        <div class="neworder-control-group"
                                                            @if ($loop->first) id="tour-neworder-discount" @endif
                                                            @if (!empty($productLimits['max_discount_percent'])) data-max-discount-percent="{{ $productLimits['max_discount_percent'] }}" @endif
                                                            @if (!empty($productLimits['max_discount_amount'])) data-max-discount-amount="{{ $productLimits['max_discount_amount'] }}" @endif>
                                                            <span class="neworder-control-group__label">
                                                                درصد تخفیف
                                                                @if (!empty($productLimits['max_discount_percent']))
                                                                    <small class="text-muted">(حداکثر {{ rtrim(rtrim(number_format($productLimits['max_discount_percent'], 2, '.', ''), '0'), '.') }}%)</small>
                                                                @endif
                                                            </span>
                                                            <div class="d-flex counterbox">
                                                                <span class="col-2 minus">-</span>
                                                                <input type="number" class="inputcount col-8 discount-input" min="0"
                                                                    max="{{ $productLimits['max_discount_percent'] ?? 100 }}"
                                                                    value="0"
                                                                    name="discount_{{ $product->id }}"
                                                                    data-product-id="{{ $product->id }}" />
                                                                <span class="col-2 plus">+</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="neworder-product-card__footer"
                                                        @if ($loop->first) id="tour-neworder-details" @endif>
                                                        <a href="#" class="btn btn-outline-secondary w-100" type="button">
                                                            <x-ui.icon name="info-circle" class="me-1" />
                                                            جزئیات محصول
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="card neworder-action-card" id="tour-neworder-submit">
                                <div class="card-body">
                                    <div class="neworder-action-bar__inner">
                                        <p class="neworder-action-bar__hint mb-0">
                                            @if ($showProducts)
                                                پس از تعیین تعداد، «ثبت سفارش» را بزنید. حداقل یک محصول با تعداد بیشتر از صفر لازم است.
                                            @else
                                                ابتدا مشتری را انتخاب کنید، سپس با «ادامه و انتخاب محصول» وارد لیست کالا شوید.
                                            @endif
                                        </p>
                                        <div class="neworder-action-bar__actions">
                                            <button type="button"
                                                class="btn set_customer {{ $initialButtonClass }} waves-effect waves-light">
                                                <x-ui.icon name="shopping-cart-plus" class="me-1" />
                                                {{ $initialButtonLabel }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        $('.orders').addClass('open')
        $('.orders .add-order').addClass('active open')

        $(function() {
            var dt_without_ajax_table = $('.datatables-direct-basic');

            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 5,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }
        });

        $('.counterbox .plus').click(function() {
            itemcount = $(this).siblings('.inputcount').val();
            if (parseInt(itemcount) > 0) {
                $(this).siblings('.inputcount').val(eval(parseInt(itemcount) + 1))
            } else {
                $(this).siblings('.inputcount').val(1)
            }
        });

        $('.counterbox .minus').click(function() {
            itemcount = $(this).siblings('.inputcount').val();
            if (parseInt(itemcount) > 0) {
                $(this).siblings('.inputcount').val(eval(parseInt(itemcount) - 1))
            }
        });

        $(document).ready(function() {
            const captureInvoiceLocation = @json($captureInvoiceLocation ?? false);
            const $submitButton = $('.set_customer');
            const $actionHint = $('.neworder-action-bar__hint');
            const $customerSelect = $('#tour-neworder-customer-select');

            if ($customerSelect.length) {
                if ($customerSelect.hasClass('select2-hidden-accessible')) {
                    $customerSelect.select2('destroy');
                }

                if (!$customerSelect.parent().hasClass('position-relative')) {
                    $customerSelect.wrap('<div class="position-relative"></div>');
                }

                $customerSelect.select2({
                    width: '100%',
                    dir: 'rtl',
                    allowClear: true,
                    placeholder: 'انتخاب کنید...',
                    dropdownParent: $(document.body),
                    language: {
                        noResults: function() {
                            return 'مشتری یافت نشد';
                        },
                        searching: function() {
                            return 'در حال جستجو...';
                        }
                    }
                });
            }

            function submitOrderForm() {
                $('#addFactor').submit();
            }

            function captureLocationThenSubmit(onUnavailable) {
                if (!captureInvoiceLocation) {
                    submitOrderForm();
                    return;
                }

                if (!navigator.geolocation) {
                    if (typeof onUnavailable === 'function') {
                        onUnavailable();
                    } else {
                        submitOrderForm();
                    }
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        $('#visitor_lat').val(position.coords.latitude);
                        $('#visitor_lng').val(position.coords.longitude);
                        submitOrderForm();
                    },
                    function() {
                        if (typeof onUnavailable === 'function') {
                            onUnavailable();
                        } else {
                            submitOrderForm();
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }

            if (captureInvoiceLocation && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        $('#visitor_lat').val(position.coords.latitude);
                        $('#visitor_lng').val(position.coords.longitude);
                    },
                    function() {},
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }

            $(document).on('change', '.fixed-qty-toggle', function() {
                const productId = $(this).data('product-id');
                $('#item_' + productId).val(this.checked ? 1 : 0);
            });

            function productIsSelected($card) {
                let selected = false;
                $card.find('input[type="number"].inputcount').each(function() {
                    if (parseInt($(this).val(), 10) > 0) {
                        selected = true;
                        return false;
                    }
                });
                if (!selected) {
                    $card.find('.fixed-qty-hidden').each(function() {
                        if (parseInt($(this).val(), 10) > 0) {
                            selected = true;
                            return false;
                        }
                    });
                }
                return selected;
            }

            function validateDiscountLimits() {
                let customerMaxDiscount = null;
                if ($customerSelect.length) {
                    const selectedOption = $customerSelect.find('option:selected');
                    const rawCustomerMax = selectedOption.data('max-discount');
                    if (rawCustomerMax !== undefined && rawCustomerMax !== '') {
                        customerMaxDiscount = parseFloat(rawCustomerMax);
                    }
                }

                let firstError = null;
                $('.pr_item').each(function() {
                    const $card = $(this);
                    if (!productIsSelected($card)) {
                        return;
                    }

                    const $discountGroup = $card.find('[data-max-discount-percent], [data-max-discount-amount]').first();
                    const $discountInput = $card.find('.discount-input');
                    const discountPercent = parseFloat($discountInput.val() || 0);
                    if (discountPercent <= 0) {
                        return;
                    }

                    const maxPercent = parseFloat($discountGroup.data('max-discount-percent'));
                    if (!isNaN(maxPercent) && maxPercent > 0 && discountPercent > maxPercent) {
                        firstError = 'درصد تخفیف از سقف مجاز (' + maxPercent + '%) بیشتر است.';
                        return false;
                    }

                    const pack = parseInt($card.find('[name^="pack_"]').val() || 0, 10);
                    const item = parseInt($card.find('[name^="item_"]').val() || 0, 10);
                    const priceRaw = ($card.find('.price').val() || $card.find('.pricepr').text() || '0').replace(/[^\d]/g, '');
                    const unitPrice = parseInt(priceRaw || 0, 10);
                    const packItems = parseInt($card.find('[data-pack-items]').data('pack-items') || 1, 10);
                    const qty = (pack * packItems) + item;
                    const gross = qty * unitPrice;
                    const discountAmount = Math.round((gross * discountPercent) / 100);

                    let maxAmount = parseFloat($discountGroup.data('max-discount-amount'));
                    if (!isNaN(customerMaxDiscount) && customerMaxDiscount > 0) {
                        maxAmount = isNaN(maxAmount) || maxAmount <= 0
                            ? customerMaxDiscount
                            : Math.min(maxAmount, customerMaxDiscount);
                    }

                    if (!isNaN(maxAmount) && maxAmount > 0 && discountAmount > maxAmount) {
                        firstError = 'مبلغ تخفیف (' + discountAmount.toLocaleString('fa-IR') + ' ریال) از سقف مجاز (' + maxAmount.toLocaleString('fa-IR') + ' ریال) بیشتر است.';
                        return false;
                    }
                });

                return firstError;
            }

            $submitButton.on('click', function() {
                $('#productValidationAlert').addClass('d-none');

                if ($(this).hasClass('btn-success')) {
                    let hasSelectedProduct = false;

                    $('.pr_item').each(function() {
                        if (productIsSelected($(this))) {
                            hasSelectedProduct = true;
                            return false;
                        }
                    });

                    if (!hasSelectedProduct) {
                        $('#productValidationAlert').removeClass('d-none').html(
                            '<x-ui.icon name="alert-circle" class="me-1" /> برای ثبت فاکتور حداقل باید یک محصول را انتخاب نمایید'
                        );
                        $('html, body').animate({
                            scrollTop: $('#productValidationAlert').offset().top - 120
                        }, 200);
                        return;
                    }

                    const discountError = validateDiscountLimits();
                    if (discountError) {
                        $('#productValidationAlert').removeClass('d-none').html(
                            '<x-ui.icon name="alert-circle" class="me-1" /> ' + discountError
                        );
                        $('html, body').animate({
                            scrollTop: $('#productValidationAlert').offset().top - 120
                        }, 200);
                        return;
                    }

                    $(this).attr('type', 'button');

                    if (!captureInvoiceLocation) {
                        submitOrderForm();
                        return;
                    }

                    if ($('#visitor_lat').val() && $('#visitor_lng').val()) {
                        submitOrderForm();
                        return;
                    }

                    captureLocationThenSubmit(submitOrderForm);

                } else {
                    if ($customerSelect.length && !$customerSelect.val()) {
                        $customerSelect.select2('open');
                        return;
                    }

                    $(this).removeClass('btn-info').addClass('btn-success')
                        .html('<x-ui.icon name="shopping-cart-plus" class="me-1" />ثبت سفارش');
                    $actionHint.text('پس از تعیین تعداد، «ثبت سفارش» را بزنید. حداقل یک محصول با تعداد بیشتر از صفر لازم است.');
                    $('.prlist').removeClass('d-none');

                    const scrollTarget = document.getElementById('tour-neworder-search')
                        || document.getElementById('tour-neworder-products-section');
                    if (scrollTarget) {
                        $('html, body').animate({
                            scrollTop: $(scrollTarget).offset().top - 120
                        }, 250);
                    }
                }
            });
        });
    </script>
    <script>
        document.querySelectorAll('.price').forEach(input => {
            input.addEventListener('input', e => {
                let pos = e.target.selectionStart;
                let raw = e.target.value.replace(/[^\d]/g, '');
                let withCommas = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                e.target.value = withCommas;
                let diff = withCommas.length - raw.length;
                e.target.selectionEnd = pos + diff;
            });
        });
    </script>
    <script>
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function() {
                let value = this.value.toLowerCase().trim();
                let items = document.querySelectorAll(".pr_item");

                items.forEach(function(item) {
                    let text = item.innerText.toLowerCase();
                    item.style.display = text.includes(value) ? "" : "none";
                });
            });
        }
    </script>
</body>

</html>
