<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>جزئیات فاکتور شماره {{ $factor->id }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Page CSS -->
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
<?php $Organ = App\Models\Organization::find($factor->organization_id); ?>
<body>
@include('sweetalert::alert')
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections.header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="pt-3 pt-1 mb-1">
                                <span class="text-muted fw-light">سفارشات و فاکتورها /</span>
                                جزئیات سفارش شماره {{ $factor->id }}
                                @if($factor->status == 0)
                                    <span class="badge bg-label-warning">در انتظار تایید سرپرست</span>
                                @elseif($factor->status == 1)
                                    <span class="badge bg-label-success">تایید شده</span>
                                @elseif($factor->status == 3)
                                    <span class="badge bg-label-danger">رد شده</span>
                                @endif
                            </h5>
                            <p class="badge btn-outline-dark">تاریخ تحویل: {{ $factor->recive_date }}</p>
                            <p class="badge btn-outline-dark">تاریخ ثبت سفارش: {{ Verta($factor->created_at)->format('H:i:s - %d %B ، %Y ') }}</p>
                        </div>
                        <div class="col-4 justify-content-end d-flex align-items-center">

                            <a href="{{ session('backlink') }}" class="btn btn-label-dark waves-effect ms-3" type="button">
                                بازگشت
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>

                        </div>
                    </div>
                    <div class="row">
                        <div class="card custom_factor mb-4">
                            <div class="card-header px-1 d-flex justify-content-between">
                                <div class="card-title col-3">
                                    <svg width="20" height="23" viewBox="0 0 20 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.72727 11.5H10.8182M6.72727 14.7308H10.8182M6.72727 17.9615H10.8182M14.0909 18.7692H16.5455C17.1964 18.7692 17.8208 18.5139 18.2811 18.0595C18.7414 17.6051 19 16.9888 19 16.3462V5.15477C19 3.93246 18.0782 2.89538 16.8444 2.79415C16.4364 2.76075 16.028 2.73203 15.6193 2.708M15.6193 2.708C15.6917 2.93963 15.7273 3.18067 15.7273 3.42308C15.7273 3.63729 15.6411 3.84273 15.4876 3.9942C15.3342 4.14567 15.1261 4.23077 14.9091 4.23077H10C9.54836 4.23077 9.18182 3.86892 9.18182 3.42308C9.18182 3.17431 9.22 2.93415 9.29091 2.708M15.6193 2.708C15.3105 1.71938 14.3767 1 13.2727 1H11.6364C11.1119 1.00012 10.6012 1.166 10.1792 1.47335C9.75714 1.7807 9.44585 2.21337 9.29091 2.708M9.29091 2.708C8.88073 2.73277 8.47273 2.76185 8.06473 2.79415C6.83091 2.89538 5.90909 3.93246 5.90909 5.15477V7.46154M5.90909 7.46154H2.22727C1.54982 7.46154 1 8.00431 1 8.67308V20.7885C1 21.4572 1.54982 22 2.22727 22H12.8636C13.5411 22 14.0909 21.4572 14.0909 20.7885V8.67308C14.0909 8.00431 13.5411 7.46154 12.8636 7.46154H5.90909ZM4.27273 11.5H4.28145V11.5086H4.27273V11.5ZM4.27273 14.7308H4.28145V14.7394H4.27273V14.7308ZM4.27273 17.9615H4.28145V17.9702H4.27273V17.9615Z" stroke="#524595" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                    <h3 class="d-inline" style="font-size: 17px">پیش فاکتور</h3>
                                </div>
                                <div class="col-9 d-flex justify-content-end gap-2">
                                    <a href="{{ route('pishFactorView',$factor->id) }}" class="btn btn-outline-primary waves-effect" type="button">
                                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.28147 8.59381C3.12407 8.61348 2.96667 8.63447 2.80927 8.65677M3.28147 8.59381C5.58096 8.30531 7.9075 8.30531 10.207 8.59381M3.28147 8.59381L3.03226 11.3293M10.207 8.59381C10.3644 8.61348 10.5218 8.63447 10.6792 8.65677M10.207 8.59381L10.4562 11.3293L10.6064 12.9839C10.6157 13.086 10.6036 13.1889 10.5709 13.2861C10.5382 13.3832 10.4857 13.4725 10.4165 13.5482C10.3474 13.6239 10.2633 13.6844 10.1695 13.7258C10.0758 13.7672 9.97437 13.7886 9.87186 13.7886H3.6166C3.18244 13.7886 2.84272 13.4161 2.88207 12.9839L3.03226 11.3293M3.03226 11.3293H2.31741C1.92605 11.3293 1.55072 11.1738 1.27399 10.8971C0.997262 10.6203 0.841797 10.245 0.841797 9.85365V5.72588C0.841797 5.01693 1.34547 4.40439 2.04655 4.29946C2.46362 4.23707 2.8819 4.18307 3.30114 4.13747M10.4549 11.3293H11.1704C11.3642 11.3293 11.5562 11.2912 11.7353 11.2171C11.9144 11.143 12.0771 11.0343 12.2142 10.8973C12.3513 10.7603 12.4601 10.5976 12.5343 10.4185C12.6085 10.2394 12.6467 10.0475 12.6467 9.85365V5.72588C12.6467 5.01693 12.143 4.40439 11.4419 4.29946C11.0248 4.23707 10.6066 4.18307 10.1873 4.13747M10.1873 4.13747C7.89868 3.88845 5.58978 3.88845 3.30114 4.13747M10.1873 4.13747V1.7378C10.1873 1.33054 9.85678 1 9.44951 1H4.03895C3.63168 1 3.30114 1.33054 3.30114 1.7378V4.13747M10.6792 6.41056H10.6844V6.41581H10.6792V6.41056ZM8.71171 6.41056H8.71695V6.41581H8.71171V6.41056Z" stroke="#524595" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                        دانلود/پرینت
                                    </a>
                                </div>
                            </div>
                            <div class="row justify-content-between mb-md-4">
                                <div class="col-6 mb-3">
                                    <div class="row px-1 mx-0 factor_dates justify-content-between">
                                            <span class="col-6  px-0">
                                            <svg width="24" height="28" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.2483 10.8462V8.69231C13.2483 7.95786 12.9497 7.2535 12.4181 6.73417C11.8866 6.21483 11.1657 5.92308 10.414 5.92308H9.15427C8.90371 5.92308 8.6634 5.82582 8.48622 5.65271C8.30904 5.4796 8.20951 5.24482 8.20951 5V3.76923C8.20951 3.03479 7.91089 2.33042 7.37936 1.81109C6.84782 1.29176 6.12691 1 5.3752 1H3.80059M3.80059 11.4615H10.099M3.80059 13.9231H6.94982M5.69013 1H1.59613C1.07462 1 0.651367 1.41354 0.651367 1.92308V16.0769C0.651367 16.5865 1.07462 17 1.59613 17H12.3035C12.825 17 13.2483 16.5865 13.2483 16.0769V8.38462C13.2483 6.42609 12.452 4.54779 11.0345 3.1629C9.61711 1.77802 7.69467 1 5.69013 1Z" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                                            تاریخ ثبت سفارش
                                        </span>
                                        <span class="col-6 text-end">{{ Verta($factor->created_at)->format('%d %B ، %Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="row px-1 mx-0 factor_dates justify-content-between">
                                            <span class="col-6 px-0">
                                            <svg width="24" height="28" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.5 1H1.9C1.4032 1 1 1.41354 1 1.92308V16.0769C1 16.5865 1.4032 17 1.9 17H12.1C12.5968 17 13 16.5865 13 16.0769V8.69231M5.5 1H5.8C7.70956 1 9.54091 1.77802 10.8912 3.1629C12.2414 4.54779 13 6.42609 13 8.38462V8.69231M5.5 1C6.21608 1 6.90284 1.29176 7.40919 1.81109C7.91554 2.33042 8.2 3.03479 8.2 3.76923V5C8.2 5.50954 8.6032 5.92308 9.1 5.92308H10.3C11.0161 5.92308 11.7028 6.21483 12.2092 6.73417C12.7155 7.2535 13 7.95786 13 8.69231M4.6 11.4615L6.4 13.3077L9.4 9" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>


                                            تاریخ تحویل
                                        </span>
                                        <span class="col-6 text-end  ps-0">{{ $factor->recive_date != null ? $factor->recive_date : 'وارد نشده' }}</span>
                                    </div>
                                </div>
                            </div>
                            <h3 style="font-size: 17px">
                                <svg width="36" height="28" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24.9994 10.3333L21.5202 14M21.5202 14L18.041 10.3333M21.5202 14V8.5V3" stroke="#A57900" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10.835 4.07692C10.835 4.89297 10.4946 5.6756 9.88867 6.25263C9.28272 6.82967 8.46088 7.15384 7.60394 7.15384C6.74699 7.15384 5.92515 6.82967 5.3192 6.25263C4.71325 5.6756 4.37283 4.89297 4.37283 4.07692C4.37283 3.26087 4.71325 2.47824 5.3192 1.90121C5.92515 1.32417 6.74699 1 7.60394 1C8.46088 1 9.28272 1.32417 9.88867 1.90121C10.4946 2.47824 10.835 3.26087 10.835 4.07692ZM1.14258 15.6609C1.17027 14.0464 1.86319 12.507 3.07192 11.3746C4.28065 10.2422 5.90832 9.60756 7.60394 9.60756C9.29955 9.60756 10.9272 10.2422 12.136 11.3746C13.3447 12.507 14.0376 14.0464 14.0653 15.6609C12.0382 16.5461 9.83396 17.0029 7.60394 17C5.29822 17 3.10968 16.5208 1.14258 15.6609Z" stroke="#A57900" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                مشخصات خریدار
                            </h3>
                            <div class="col-12 buyer_info mb-4">
                                <div class="row mx-0">
                                    <div class="col title">نام</div>
                                    <div class="col">{{ $factor->customer->name }} - {{ $factor->customer->tablo }}</div>
                                    <div class="col title">شماره تماس</div>
                                    <div class="col">{{ $factor->customer->phone }}</div>
                                </div>
                                <div class="row mx-0">
                                    <div class="col title">کدپستی</div>
                                    <div class="col">{{ $factor->customer->zipcode }}</div>
                                    <div class="col title">شماره اقتصادی</div>
                                    <div class="col">{{ $factor->customer->buyer_econimic_code }}</div>
                                </div>
                                <div class="row mx-0">
                                    <div class="col col-3 title">آدرس</div>
                                    <div class="col">{{ $factor->customer->address }}</div>
                                </div>
                            </div>

                            <h3 style="font-size: 17px">
                                <svg width="36" height="28" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M25 6.66667L21.5195 3M21.5195 3L18.0391 6.66667M21.5195 3V8.5V14" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10.8308 4.07692C10.8308 4.89297 10.4902 5.6756 9.88405 6.25263C9.27787 6.82967 8.45573 7.15384 7.59847 7.15384C6.74122 7.15384 5.91908 6.82967 5.3129 6.25263C4.70673 5.6756 4.36619 4.89297 4.36619 4.07692C4.36619 3.26087 4.70673 2.47824 5.3129 1.90121C5.91908 1.32417 6.74122 1 7.59847 1C8.45573 1 9.27787 1.32417 9.88405 1.90121C10.4902 2.47824 10.8308 3.26087 10.8308 4.07692ZM1.13477 15.6609C1.16246 14.0464 1.85564 12.507 3.06481 11.3746C4.27398 10.2422 5.90225 9.60756 7.59847 9.60756C9.2947 9.60756 10.923 10.2422 12.1321 11.3746C13.3413 12.507 14.0345 14.0464 14.0622 15.6609C12.0344 16.5461 9.82931 17.0029 7.59847 17C5.29192 17 3.10258 16.5208 1.13477 15.6609Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                مشخصات فروشنده
                            </h3>
                            <?php $factorMaker = App\Models\factorMaker::where('organization_id',$factor->organization_id)->first(); ?>
                            <div class="col-12 buyer_info mb-4">
                                <div class="row mx-0">
                                    <div class="col title">نام</div>
                                    <div class="col">{{ isset($factorMaker->seller_name) ? $factorMaker->seller_name : '' }}</div>
                                    <div class="col title">شماره تماس</div>
                                    <div class="col">{{ isset($factorMaker->seller_phone) ? $factorMaker->seller_phone : ''  }}</div>
                                </div>
                                <div class="row mx-0">
                                    <div class="col title">کدپستی</div>
                                    <div class="col">{{ isset($factorMaker->seller_zip_code) ? $factorMaker->seller_zip_code : '' }}</div>
                                    <div class="col title">شماره اقتصادی</div>
                                    <div class="col">{{ isset($factorMaker->seller_economic_number) ? $factorMaker->seller_economic_number : '' }}</div>
                                </div>
                                <div class="row mx-0">
                                    <div class="col col-3 title">آدرس</div>
                                    <div class="col">{{ isset($factorMaker->seller_address) ? $factorMaker->seller_address : '' }}</div>
                                </div>
                            </div>

                            <h3 style="font-size: 17px">
                                <svg width="36" height="36" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 6.11538L10 1L1 6.11538M19 6.11538L10 11.2308M19 6.11538V14.8846L10 20M1 6.11538L10 11.2308M1 6.11538V14.8846L10 20M10 11.2308V20" stroke="#524595" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                مشخصات محصولات
                            </h3>
                            <style>
                                .box {
                                    max-width: 60px;
                                    width: 45px;
                                    min-width: 45px;
                                }
                                @media(max-width: 1024px) {
                                    .box {
                                        max-width: 45px;
                                        width: 45px;
                                        min-width: 45px;
                                    }
                                }
                            </style>
                            <div class="col-12 buyer_info mb-4">
                                <div class="row mx-0">
                                    <div class="col title" style="max-width: 40px;">#</div>
                                    <div class="col title" style="min-width: 75px;width: 85px;max-width: 85px">کد کالا</div>
                                    <div class="col title" style="min-width: 75px">نام کالا</div>
                                    <div class="col title box" >کارتن</div>
                                    <div class="col title box">جزء</div>
                                    <div class="col title box">کل</div>
                                    <div class="col title" style="min-width:95px;width: 95px;max-width: 95px">فی واحد</div>
                                    <div class="col title" style="max-width: 95px">مبلغ ناخالص</div>
                                    <div class="col title" style="max-width: 95px">درصد تخفیف</div>
                                    <div class="col title" style="max-width: 95px">مبلغ تخفیف</div>
                                    <div class="col title" style="width: 145px;min-width: 145px;max-width: 145px;">مبلغ خالص</div>
                                </div>

                                @php($x = 1)
                                @php($allpacks = 0)
                                @php($allitems = 0)
                                @php($allitems_full = 0)
                                @php($item_fees = 0)
                                @php($all_item_fees = 0)
                                @php($all_item_tax = 0)
                                @php($all_discounts  = 0)
                                @php($all_pats = 0)
                                @php($factor_price = 0)
                                @foreach($Items as $item)
                                    <?php $pr = DB::table('products')->where('id', $item->pr_id)->first() ?>
                                    <div class="row mx-0">
                                        <div class="col text-center" style="max-width: 40px;">{{ $x }}</div>
                                        <div class="col text-center" style="min-width: 75px;width: 85px;max-width: 85px;font-size: 12px">{{ $pr->sku }}</div>
                                        <div class="col text-center" style="min-width: 75px">{{ $pr->title }} {{ $pr->display_name }}</div>
                                        <div class="col text-center box" >{{ intval($item->pack) }} @php($allpacks += intval($item->pack))</div>
                                        <div class="col text-center box">{{ intval($item->tedad) }} @php($allitems += intval($item->tedad))</div>
                                        @php($items = intval($pr->pack_items) * intval($item->pack) + intval($item->tedad))
                                        @php($allitems_full += intval($items))
                                        <div class="col text-center box">{{ intval($items) }}</div>
                                        <div class="col text-center" style="min-width:95px;width: 95px;max-width: 95px">{{ intval($item->price) > 0 ? number_format($item->price) : 0 }}</div>
                                        @php($fee_price = intval($items) * intval($item->price))
                                        @php($all_item_fees += $fee_price)
                                        @php($item_fees += intval($item->price) )
                                        @php($disprice = (intval($items) * intval($item->price)) * intval($item->discount) / 100)
                                        @php($pat = intval($fee_price) - intval($disprice))
                                        @php($all_discounts += $disprice)
                                        @php($all_pats += $pat)
                                        @php($taxprice = intval(($pat * $pr->tax) / 100))
                                        @php($all_item_tax += $taxprice)
                                        <div class="col text-center" style="max-width: 95px">{{ number_format($fee_price) }}</div>
                                        <div class="col text-center" style="max-width: 95px">{{ "%".intval($item->discount) }}</div>
                                        <div class="col text-center" style="max-width: 95px;font-size: 14px">{{ number_format($disprice) }}</div>
                                        <div class="col text-center" style="width: 145px;min-width: 145px;max-width: 145px;">
                                            @php($fullp = intval($pat) + intval($taxprice))
                                                <?php /* number_format($fullp) */ ?>
                                            {{ number_format($pat) }}
                                            @php($factor_price += $fullp)
                                        </div>
                                    </div>
                                    @php($x++)
                                @endforeach
                                <div class="row mx-0 gray">
                                    <div class="col title text-end">جمع کل</div>
                                    <div class="col text-center" style="width: 145px;min-width: 145px;max-width: 145px;">{{ number_format($all_item_fees) }} <small>ریال</small></div>
                                </div>
                                <div class="row mx-0 gray">
                                    <div class="col title text-end">مجموع تخفیف</div>
                                    <div class="col text-center" style="width: 145px;min-width: 145px;max-width: 145px;">{{ number_format($all_discounts) }} <small>ریال</small></div>
                                </div>
                                <div class="row mx-0 gray">
                                    <div class="col title text-end">ارزش افزوده</div>
                                    <div class="col text-center" style="width: 145px;min-width: 145px;max-width: 145px;">{{ number_format($all_item_tax) }}</div>
                                </div>

                                <div class="row mx-0 gray">
                                    <div class="col title text-end">مجموع قابل پرداخت</div>
                                    <div class="col text-center" style="width: 145px;min-width: 145px;max-width: 145px;">{{ number_format($factor_price) }} <small>ریال</small></div>
                                </div>
                            </div>


                        </div>
                        <form method="POST" id="updateData" class="px-0" action="{{ route('pishFactorUpdate',$factor->id) }}" >
                            @csrf
                        <div class="card order-1 order-md-2 mb-3">
                            <div class="card-header pb-0 px-1 d-flex justify-content-between">
                                <div class="card-title col-4">
                                    <svg width="32" height="28" viewBox="0 0 22 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.75039 6.776C2.86239 6.759 2.97739 6.75 3.09439 6.75H18.9064C19.0234 6.75 19.1384 6.759 19.2504 6.776M2.75039 6.776C2.16516 6.86663 1.63925 7.18434 1.28674 7.66022C0.934239 8.13609 0.783561 8.73175 0.867393 9.318L1.72439 15.318C1.80094 15.8541 2.06829 16.3447 2.47737 16.6996C2.88646 17.0545 3.40982 17.2499 3.95139 17.25H18.0504C18.592 17.2499 19.1153 17.0545 19.5244 16.6996C19.9335 16.3447 20.2009 15.8541 20.2774 15.318L21.1344 9.318C21.2182 8.73175 21.0675 8.13609 20.715 7.66022C20.3625 7.18434 19.8356 6.86663 19.2504 6.776M2.75039 6.776L2.75139 3C2.75139 2.40344 2.98831 1.83129 3.41005 1.40936C3.83179 0.987435 4.40383 0.750265 5.00039 0.75H8.87939C9.27708 0.75035 9.65836 0.908615 9.93939 1.19L12.0614 3.31C12.3424 3.59138 12.7237 3.74965 13.1214 3.75H17.0004C17.5971 3.75 18.1694 3.98705 18.5914 4.40901C19.0133 4.83097 19.2504 5.40326 19.2504 6V6.776" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>


                                    <h3 class="d-inline" style="font-size: 17px">وضعیت سفارش</h3>
                                </div>
                            </div>
                            <hr class="my-1" />

                                <div class="row flex-wrap">
                                    
                                    <div class="col-12 mb-2">
                                        <div class="card-body py-1">
                                            <label for="tozihat">توضیحات سفارش</label>
                                            <textarea class="form-control" name="tozihat" id="tozihat">{{ $factor->tozihat }}</textarea>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <div class="card order-1 order-md-2 mb-3">
                            <div class="card-header pb-0 px-1 d-flex justify-content-between">
                                <div class="card-title col-4">
                                    <svg width="32" height="26" viewBox="0 0 22 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.25 4.25H20.75M1.25 5H20.75M4.25 10.25H10.25M4.25 12.5H7.25M3.5 15.5H18.5C19.0967 15.5 19.669 15.2629 20.091 14.841C20.5129 14.419 20.75 13.8467 20.75 13.25V2.75C20.75 2.15326 20.5129 1.58097 20.091 1.15901C19.669 0.737053 19.0967 0.5 18.5 0.5H3.5C2.90326 0.5 2.33097 0.737053 1.90901 1.15901C1.48705 1.58097 1.25 2.15326 1.25 2.75V13.25C1.25 13.8467 1.48705 14.419 1.90901 14.841C2.33097 15.2629 2.90326 15.5 3.5 15.5Z" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <h3 class="d-inline" style="font-size: 17px">نحوه پرداخت</h3>
                                </div>
                            </div>
                            <hr class="my-1" />
                                <div class="row mx-0">
                                    <div class="col-md mb-md-0 mb-2">
                                        <div class="form-check custom-option custom-option-basic">
                                            <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                                <input @if($factor->payment_type == 1) checked @endif class="form-check-input" id="customRadioTemp1" name="payment_type" type="radio" value="1"/>
                                                <span class="custom-option-header">
                                                        <span class="h6 mb-0">پرداخت نقدی</span>
                                                    </span>
                                                <span class="custom-option-body">
                                                        <small>پرداخت نقدی</small>
                                                    </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md mb-md-0 mb-2">
                                        <div class="form-check custom-option custom-option-basic">
                                            <label class="form-check-label custom-option-content" for="customRadioTemp2">
                                                <input @if($factor->payment_type == 2) checked @endif class="form-check-input" id="customRadioTemp2" name="payment_type" type="radio" value="2"/>
                                                <span class="custom-option-header">
                                                        <span class="h6 mb-0">دریافت چک</span>
                                                    </span>
                                                <span class="custom-option-body">
                                                        <small>چک 30 روزه</small>
                                                    </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md mb-md-0 mb-2">
                                        <div class="form-check custom-option custom-option-basic">
                                            <label class="form-check-label custom-option-content" for="heyndaryaft">
                                                <input @if($factor->payment_type == 3) checked @endif class="form-check-input" id="heyndaryaft" name="payment_type" type="radio" value="3"/>
                                                <span class="custom-option-header">
                                                        <span class="h6 mb-0">پرداخت حین دریافت</span>
                                                    </span>
                                                <span class="custom-option-body">
                                                        <small>پرداخت توسط راننده دریافت میشود.</small>
                                                    </span>
                                            </label>
                                        </div>
                                    </div>
                                    <p style="color: red;" id="PaymentError"></p>
                                </div>
                                <div class="row mx-3 mt-3">
                                    <p class="col-12 col-md-10 alert alert-danger">تاریخ مندرج روی چک نهایتا {{ verta()->addMonth()->format('d-m-Y') }} میباشد.</p>
                                    <div class="col-2 pe-0 text-end">
                                        <button class="btn btn-primary py-3 w-100 waves-effect" type="submit">تایید</button>
                                    </div>
                                </div>

                        </div>
                        </form>
                        <div class="card order-1 order-md-2 mb-3">
                            <div class="card-header pb-0 px-1 d-flex justify-content-between">
                                <div class="card-title col-4">
                                    <svg width="32" height="30" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 1V18.25M11 18.25C9.528 18.25 8.118 18.515 6.815 19M11 18.25C12.472 18.25 13.882 18.515 15.185 19M17.75 2.97C15.5137 2.65611 13.2582 2.49906 11 2.5C8.709 2.5 6.455 2.66 4.25 2.97M17.75 2.97C18.76 3.113 19.76 3.287 20.75 3.49M17.75 2.97L20.37 13.696C20.492 14.195 20.264 14.724 19.781 14.898C19.1294 15.1319 18.4423 15.2509 17.75 15.25C17.0577 15.2509 16.3706 15.1319 15.719 14.898C15.236 14.724 15.008 14.195 15.129 13.696L17.75 2.971V2.97ZM4.25 2.97C3.24 3.113 2.24 3.287 1.25 3.49M4.25 2.97L6.87 13.696C6.992 14.195 6.764 14.724 6.281 14.898C5.62943 15.1318 4.94226 15.2509 4.25 15.25C3.55774 15.2509 2.87057 15.1318 2.219 14.898C1.736 14.724 1.508 14.195 1.629 13.696L4.25 2.971V2.97Z" stroke="#F9BA16" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                    <h3 class="d-inline" style="font-size: 17px">وضعیت حساب مشتری</h3>
                                </div>
                            </div>
                            <hr class="my-1" />
                            <div class="col-12 buyer_info mb-4">
                                <div class="row mx-0">
                                    <div class="col title" style="max-width: 200px;">مانده بدهی/طلب گذشته <svg width="21" height="13" viewBox="0 0 21 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.02464 10.6968C8.00377 10.7958 7.31096 11.9684 7.1941 12.0873C6.39277 12.9113 4.05139 13.1014 2.9287 12.9548C-0.0804506 12.5627 0.766785 9.27463 1.16327 7.25428L1.97295 7.23051C0.800174 11.5168 3.17077 12.8518 7.08976 10.9979L6.76005 1.22888C8.07055 0.602964 8.50043 -0.633015 9.90692 0.393007C12.24 2.10436 10.4829 5.46766 11.4178 7.6742C11.9728 8.98545 14.9736 8.25257 16.1673 8.22088L15.5788 5.57065L16.4845 4.85363C17.3735 6.7789 17.9369 9.32613 15.0196 9.60343C13.492 9.75001 10.6498 9.96789 10.341 8.10996C9.96536 5.85984 11.1673 1.74783 8.03299 0.892152V10.6968H8.02464Z" fill="#1C1C1C"/>
                                            <path d="M15.1211 11.7863C16.8239 11.9804 20.0835 10.9148 19.9124 8.93801C19.7913 7.52773 17.9383 6.04614 19.8164 5.25385C21.9992 8.54979 20.2379 13.7671 15.5301 12.8836L15.1211 11.7863Z" fill="#1C1C1C"/>
                                            <path d="M9.27539 12.7771V11.7867C10.7612 11.9333 12.2052 11.2559 13.6535 11.7946L13.449 12.7771H9.27539Z" fill="#1C1C1C"/>
                                        </svg>
                                    </div>
                                    <div class="col">{{ number_format($MandeCustomer) }}</div>
                                </div>
                                <div class="row mx-0">
                                    <div class="col title" style="max-width: 200px;">مقدار فاکتور جدید <svg width="21" height="13" viewBox="0 0 21 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.02464 10.6968C8.00377 10.7958 7.31096 11.9684 7.1941 12.0873C6.39277 12.9113 4.05139 13.1014 2.9287 12.9548C-0.0804506 12.5627 0.766785 9.27463 1.16327 7.25428L1.97295 7.23051C0.800174 11.5168 3.17077 12.8518 7.08976 10.9979L6.76005 1.22888C8.07055 0.602964 8.50043 -0.633015 9.90692 0.393007C12.24 2.10436 10.4829 5.46766 11.4178 7.6742C11.9728 8.98545 14.9736 8.25257 16.1673 8.22088L15.5788 5.57065L16.4845 4.85363C17.3735 6.7789 17.9369 9.32613 15.0196 9.60343C13.492 9.75001 10.6498 9.96789 10.341 8.10996C9.96536 5.85984 11.1673 1.74783 8.03299 0.892152V10.6968H8.02464Z" fill="#1C1C1C"/>
                                            <path d="M15.1211 11.7863C16.8239 11.9804 20.0835 10.9148 19.9124 8.93801C19.7913 7.52773 17.9383 6.04614 19.8164 5.25385C21.9992 8.54979 20.2379 13.7671 15.5301 12.8836L15.1211 11.7863Z" fill="#1C1C1C"/>
                                            <path d="M9.27539 12.7771V11.7867C10.7612 11.9333 12.2052 11.2559 13.6535 11.7946L13.449 12.7771H9.27539Z" fill="#1C1C1C"/>
                                        </svg>
                                    </div>
                                    <div class="col">{{ number_format($factor_price) }}</div>
                                </div>
                                <div class="row mx-0 gray">
                                    <div class="col title" style="max-width: 200px;">مانده بدهی/طلب مشتری <svg width="21" height="13" viewBox="0 0 21 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.02464 10.6968C8.00377 10.7958 7.31096 11.9684 7.1941 12.0873C6.39277 12.9113 4.05139 13.1014 2.9287 12.9548C-0.0804506 12.5627 0.766785 9.27463 1.16327 7.25428L1.97295 7.23051C0.800174 11.5168 3.17077 12.8518 7.08976 10.9979L6.76005 1.22888C8.07055 0.602964 8.50043 -0.633015 9.90692 0.393007C12.24 2.10436 10.4829 5.46766 11.4178 7.6742C11.9728 8.98545 14.9736 8.25257 16.1673 8.22088L15.5788 5.57065L16.4845 4.85363C17.3735 6.7789 17.9369 9.32613 15.0196 9.60343C13.492 9.75001 10.6498 9.96789 10.341 8.10996C9.96536 5.85984 11.1673 1.74783 8.03299 0.892152V10.6968H8.02464Z" fill="#1C1C1C"/>
                                            <path d="M15.1211 11.7863C16.8239 11.9804 20.0835 10.9148 19.9124 8.93801C19.7913 7.52773 17.9383 6.04614 19.8164 5.25385C21.9992 8.54979 20.2379 13.7671 15.5301 12.8836L15.1211 11.7863Z" fill="#1C1C1C"/>
                                            <path d="M9.27539 12.7771V11.7867C10.7612 11.9333 12.2052 11.2559 13.6535 11.7946L13.449 12.7771H9.27539Z" fill="#1C1C1C"/>
                                        </svg>
                                    </div>
                                    <div class="col">
                                        @if($factor->payment_type == null || $factor->payment_type == 3)
                                            {{ number_format(intval($MandeCustomer+$factor_price )) }}
                                        @else
                                            {{ number_format($MandeCustomer) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="row mb-3">
                        <div class="col-6 ps-0">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between py-2">
                                    <div class="card-title mb-0">
                                        <h5 class="m-0 me-2">اطلاعات مشتری</h5>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-borderless border-top">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-start align-items-center mt-lg-2">
                                                    <div class="avatar me-3 avatar-sm">
                                                        <img alt="آواتار" class="rounded-circle" src="{{ asset('assets/') }}/img/avatars/1.png">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-0">{{ $factor->customer->name }}</h6>
                                                        <small class="text-truncate text-muted">{{ $factor->customer->tablo }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <button class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2" type="button">
                                                    <span class="text-dark">
                                                        <svg width="20" height="23" viewBox="0 0 12 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.50033 3.5C8.50033 4.16304 8.23691 4.79892 7.768 5.26776C7.2991 5.7366 6.66313 6 6 6C5.33687 6 4.7009 5.7366 4.232 5.26776C3.76309 4.79892 3.49967 4.16304 3.49967 3.5C3.49967 2.83696 3.76309 2.20107 4.232 1.73223C4.7009 1.26339 5.33687 1 6 1C6.66313 1 7.2991 1.26339 7.768 1.73223C8.23691 2.20107 8.50033 2.83696 8.50033 3.5ZM1 12.912C1.02143 11.6002 1.55763 10.3494 2.49298 9.42936C3.42833 8.50928 4.68788 7.99364 6 7.99364C7.31212 7.99364 8.57166 8.50928 9.50702 9.42936C10.4424 10.3494 10.9786 11.6002 11 12.912C9.43138 13.6312 7.72566 14.0023 6 14C4.21576 14 2.5222 13.6107 1 12.912Z" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                                                        تعداد سفارش:
                                                    </span>
                                                    <strong class="text-dark">{{ number_format($Customer_Factors) }} سفارش</strong>
                                                </button>
                                                <a href="#" class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between">
                                                    <span class="text-dark">
                                                        <svg width="20" height="20" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13 7C13 6.56601 12.842 6.14979 12.5607 5.84292C12.2794 5.53604 11.8978 5.36364 11.5 5.36364H9C9 5.94229 8.78929 6.49724 8.41421 6.90642C8.03914 7.31559 7.53043 7.54545 7 7.54545C6.46957 7.54545 5.96086 7.31559 5.58579 6.90642C5.21071 6.49724 5 5.94229 5 5.36364H2.5C2.10218 5.36364 1.72064 5.53604 1.43934 5.84292C1.15804 6.14979 1 6.56601 1 7M13 7V11.3636C13 11.7976 12.842 12.2138 12.5607 12.5207C12.2794 12.8276 11.8978 13 11.5 13H2.5C2.10218 13 1.72064 12.8276 1.43934 12.5207C1.15804 12.2138 1 11.7976 1 11.3636V7M13 7V4.81818M1 7V4.81818M13 4.81818C13 4.38419 12.842 3.96798 12.5607 3.6611C12.2794 3.35422 11.8978 3.18182 11.5 3.18182H2.5C2.10218 3.18182 1.72064 3.35422 1.43934 3.6611C1.15804 3.96798 1 4.38419 1 4.81818M13 4.81818V2.63636C13 2.20237 12.842 1.78616 12.5607 1.47928C12.2794 1.1724 11.8978 1 11.5 1H2.5C2.10218 1 1.72064 1.1724 1.43934 1.47928C1.15804 1.78616 1 2.20237 1 2.63636V4.81818" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                                                        مجموع خرید: </span>
                                                    <strong class="text-dark">{{ number_format($CustomerFactorsPriceCount) }} ریال</strong>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 pe-0">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between py-2">
                                    <div class="card-title mb-0">
                                        <h5 class="m-0 me-2">اطلاعات واحد فروش</h5>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-borderless border-top">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-start align-items-center mt-lg-2">
                                                    <div class="avatar me-3 avatar-sm">
                                                        <img alt="آواتار" class="rounded-circle" src="{{ asset('assets/') }}/img/avatars/1.png">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-0">{{ $factor->visitor->name }}</h6>
                                                        @foreach ($factor->visitor->roles as $role)
                                                            <small class="text-truncate text-muted">{{ $role->description }}</small>
                                                        @endforeach

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <button class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2" type="button">
                                                    <span class="text-dark">
                                                        <svg width="20" height="23" viewBox="0 0 12 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.50033 3.5C8.50033 4.16304 8.23691 4.79892 7.768 5.26776C7.2991 5.7366 6.66313 6 6 6C5.33687 6 4.7009 5.7366 4.232 5.26776C3.76309 4.79892 3.49967 4.16304 3.49967 3.5C3.49967 2.83696 3.76309 2.20107 4.232 1.73223C4.7009 1.26339 5.33687 1 6 1C6.66313 1 7.2991 1.26339 7.768 1.73223C8.23691 2.20107 8.50033 2.83696 8.50033 3.5ZM1 12.912C1.02143 11.6002 1.55763 10.3494 2.49298 9.42936C3.42833 8.50928 4.68788 7.99364 6 7.99364C7.31212 7.99364 8.57166 8.50928 9.50702 9.42936C10.4424 10.3494 10.9786 11.6002 11 12.912C9.43138 13.6312 7.72566 14.0023 6 14C4.21576 14 2.5222 13.6107 1 12.912Z" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                                                        سرپرست:
                                                    </span>
                                                    <?php $Leader = App\Models\User::find($factor->visitor->leader_id); ?>
                                                    <strong class="text-dark">{{ isset($Leader) ? $Leader->name : 'ندارد' }}</strong>
                                                </button>
                                                <a href="#" class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between">
                                                    <span class="text-dark">
                                                        <svg width="20" height="20" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13 7C13 6.56601 12.842 6.14979 12.5607 5.84292C12.2794 5.53604 11.8978 5.36364 11.5 5.36364H9C9 5.94229 8.78929 6.49724 8.41421 6.90642C8.03914 7.31559 7.53043 7.54545 7 7.54545C6.46957 7.54545 5.96086 7.31559 5.58579 6.90642C5.21071 6.49724 5 5.94229 5 5.36364H2.5C2.10218 5.36364 1.72064 5.53604 1.43934 5.84292C1.15804 6.14979 1 6.56601 1 7M13 7V11.3636C13 11.7976 12.842 12.2138 12.5607 12.5207C12.2794 12.8276 11.8978 13 11.5 13H2.5C2.10218 13 1.72064 12.8276 1.43934 12.5207C1.15804 12.2138 1 11.7976 1 11.3636V7M13 7V4.81818M1 7V4.81818M13 4.81818C13 4.38419 12.842 3.96798 12.5607 3.6611C12.2794 3.35422 11.8978 3.18182 11.5 3.18182H2.5C2.10218 3.18182 1.72064 3.35422 1.43934 3.6611C1.15804 3.96798 1 4.38419 1 4.81818M13 4.81818V2.63636C13 2.20237 12.842 1.78616 12.5607 1.47928C12.2794 1.1724 11.8978 1 11.5 1H2.5C2.10218 1 1.72064 1.1724 1.43934 1.47928C1.15804 1.78616 1 2.20237 1 2.63636V4.81818" stroke="#543C92" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                                                        مجموع فروش: </span>
                                                    <strong class="text-dark">0 ریال</strong>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- / Content -->
                @include('sections.footer')
                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
</div>
<!-- / Layout wrapper -->
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
<!-- Vendors JS -->

<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.js"></script>
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script src="{{ asset('/js/wordifyfa.min.js') }}"></script>

<script>
    $('.factors').addClass('open')
    @if($factor->status == 0)
    $('.factors .waiting').addClass('active open');
    @elseif($factor->status == 1)
    $('.factors .accepted').addClass('active open');
    @elseif($factor->status == 3)
    $('.factors .denciled').addClass('active open');
    @elseif($factor->status == 4)
    $('.factors .compeleted ').addClass('active open');
    @endif

    $(document).ready(function() {
        jalaliDatepicker.startWatch();
        document.querySelector("[data-jdp-miladi-input]").addEventListener("jdp:change", function (e) {
            var miladiInput = document.getElementById(this.getAttribute("data-jdp-miladi-input"));
            if (!this.value) {
                miladiInput.value = "";
                return;
            }
            var date = this.value.split("/");
            miladiInput.value = jalali_to_gregorian(date[0], date[1], date[2]).join("/")
        });

        function jalali_to_gregorian(jy, jm, jd) {
            jy = Number(jy);
            jm = Number(jm);
            jd = Number(jd);
            var gy = (jy <= 979) ? 621 : 1600;
            jy -= (jy <= 979) ? 0 : 979;
            var days = (365 * jy) + ((parseInt(jy / 33)) * 8) + (parseInt(((jy % 33) + 3) / 4))
                + 78 + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
            gy += 400 * (parseInt(days / 146097));
            days %= 146097;
            if (days > 36524) {
                gy += 100 * (parseInt(--days / 36524));
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * (parseInt((days) / 1461));
            days %= 1461;
            gy += parseInt((days - 1) / 365);
            if (days > 365) days = (days - 1) % 365;
            var gd = days + 1;
            var sal_a = [0, 31, ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            var gm
            for (gm = 0; gm < 13; gm++) {
                var v = sal_a[gm];
                if (gd <= v) break;
                gd -= v;
            }
            return [gy, gm, gd];
        }


        var sum_price_with_tax = $('.full_prices span').html();
        var sum = sum_price_with_tax.replaceAll(",", "");
        $('.factor_table th.horof').html(wordifyfa(sum)+" {{ org_currency_label($Organ) }} " );



        $('#step').on('change',function() {
            var stp = $(this).val();
            if(stp == 3) {
                $('.select_driver').removeClass('d-none');
            }else {
                $('.select_driver').addClass('d-none');
            }
        });


    });

    document.getElementById('updateData').addEventListener('submit', function(e) {
        const checked = document.querySelector('#updateData input[name="payment_type"]:checked');

        if (!checked) {
            e.preventDefault(); // جلوگیری از ارسال فرم
            document.getElementById('PaymentError').innerHTML = "روش پرداخت باید انتخاب شود."
        }else {
            document.getElementById('PaymentError').innerHTML = ""
        }
    });

</script>

</body>

</html>
