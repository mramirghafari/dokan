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
                                @if(count($CARDEX) > 0)
                                    <table border="1" width="100%">

                                        <tr class="text-center">
                                            <th width="40">ردیف</th>
                                            <th width="8%">تاریخ</th>
                                            <th width="6%">سند</th>
                                            <th width="8%">نوع</th>
                                            <th width="5%">مقدار وارده</th>
                                            <th width="10%">مقدار وارده واحد فرفی</th>
                                            <th width="8%">فی وارده</th>
                                            <th width="8%">مبلغ وارده</th>
                                            <th width="5%">مقدار صادره</th>
                                            <th width="10%">مقدار صادره واحد فرعی</th>
                                            <th width="8%">فی صادره</th>
                                            <th width="8%">مبلغ صادره</th>
                                            <th width="8%">مقدار مانده</th>
                                            <th width="8%">باقی مانده واحد فرعی</th>
                                        </tr>


                                        @php $Mande = 0 @endphp
                                        @php($ImportJJ = 0)
                                        @php($x = 1)
                                            @foreach ($CARDEX as $item)
                                            <tr class="text-center">
                                                <td>{{ $x }}</td>
                                                <td>{{ $item['date'] }}</td>
                                                <td>
                                                    @if($item['import'] > 0)رسید @else خروج @endif
                                                </td>
                                                <td>
                                                    @if($item['import'] > 0)خرید (داخلی) @else فروش @endif
                                                </td>
                                                <td>
                                                    @if($item['import'] > 0)
                                                        {{ $item['import'] }}
                                                        @php($Mande += $item['import'] )
                                                        @php($ImportJJ += $item['import'])
                                                    @endif
                                                </td>
                                                <td>؟؟؟</td>
                                                <td>
                                                    @if($item['import'] > 0)
                                                        {{ number_format($item['price']) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['import'] > 0)
                                                       {{ number_format(intval($item['price']) * intval($item['import'])) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['export'] > 0)
                                                        {{ $item['export'] }}-
                                                        @php($Mande = $Mande - $item['export'] )
                                                    @endif
                                                </td>
                                                <td>---</td>
                                                <td>
                                                    @if($item['export'] > 0)
                                                        {{ number_format($item['price']) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['export'] > 0)
                                                        {{ number_format(intval($item['price']) * intval($item['export'])) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $Mande }}
                                                </td>
                                                <td>---</td>
                                            </tr>
                                                @php($x++)
                                            @endforeach

                                        <tr>
                                            <th colspan="2">مجموع</th>
                                            <th class="text-center"></th>
                                            <th></th>
                                            <th class="text-center">{{ $ImportJJ }}</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>

                                    </table>
                                @endif
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
