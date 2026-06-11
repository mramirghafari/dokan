@extends('layouts.master')
@section('title', 'گزارشات مالی حسابداری')

@section('content')
    <div class="main-content">
        <nav aria-label="خرده نان" class="container-fluid">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">پیشخوان</a></li>
                <li class="breadcrumb-item active" aria-current="page">گزارشات حسابداری و مالی</li>
            </ol>
        </nav>
        <div class="data-table-area">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-12 box-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-2 d-inline-block">گزارشات حسابداری و مالی</h4> <span class="text-info"> {{ verta()->format('Y-m-d') }}</span>
                                <hr>
                                <form action="{{ route('Accounting.PrFilter') }}" method="POST">
                                    @csrf
                                    <div class="row">

                                            <div class="form-group col-6 col-md-4">
                                                <label for="from_date">انتخاب محصول:</label>
                                                <select class="form-control" name="pr_id">
                                                    <option>-- انتخاب کنید --</option>
                                                    @foreach($Products as $pr )
                                                        <option value="{{ $pr->id }}">{{ $pr->title }} {{ $pr->display_name }}</option>

                                                    @endforeach
                                                </select>
                                            </div>


                                            <div class="form-group col-6 col-md-4">
                                                <label for="from_date">نمایش از تاریخ:</label>
                                                <input type="text" class="form-control" name="from_date"
                                                       id="from_date" data-jdp>
                                            </div>

                                            <div class="form-group col-6 col-md-4">
                                                <label for="to_date">نمایش تا تاریخ:</label>
                                                <input type="text" class="form-control" name="to_date"
                                                       id="to_date" data-jdp>
                                            </div>
                                        <div class="form-group col-6 col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-info">فیلتر تاریخ</button>
                                        </div>

                                    </div>
                                </form>
                                @if($fromDate == null && $toDate == null)
                                    <p class="text-danger">لیست زیر مربوط به کلیه سفارشات تایید شده میباشد. در صورتی که حواله مربوط به تاریخ خاصی مد نظر هست لطفا از طریق فیلتر تاریخ را انتخاب کنید.</p>
                                @elseif($fromDate != null && $toDate != null && $fromDate == $toDate)
                                    <p class="text-danger">حواله خروج مربوط به تاریخ <span style="display: inline-block;direction: ltr">{{ $fromDate }}</span> میباشد</p>
                                @elseif($fromDate != null && $toDate != null && $fromDate != $toDate)
                                    <p class="text-danger">حواله خروج مربوط به تاریخ <span style="display: inline-block;direction: ltr">{{ $fromDate }}</span> تا <span style="display: inline-block;direction: ltr">{{ $toDate }}</span> میباشد</p>
                                @endif

                                <p>تعداد فاکتور های پرداخت شده به صورت نقدی <strong>{{ count($PishFactors) }}</strong> از {{ count($AllFactors) }} فاکتور میباشد</p>
                                <p>تعداد فاکتور های پرداخت شده به صورت چکی <strong>{{ count($Checki) }}</strong> از {{ count($AllFactors) }} فاکتور میباشد</p>
                                <p>تعداد فاکتور های بدون وضعیت پرداختی <strong>{{ count($unpayed) }}</strong> از {{ count($AllFactors) }} فاکتور میباشد</p>

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->

            </div>
        </div>
    </div>



@endsection

@section('scripts')
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
        });
    </script>
@endsection
