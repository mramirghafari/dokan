@extends('layouts.master')
@section('title','سطل زباله کالاهای دست دوم')

@section('content')
<div class="main-content">
    <nav aria-label="خرده نان" class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">پیشخوان</a></li>
            <li class="breadcrumb-item active" aria-current="page">سطل زباله کالاهای دست دوم</li>
        </ol>
    </nav>
    <div class="data-table-area">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12 col-lg-12 box-margin">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-2">کالاهای دست دوم حذف شده</h4>
                            <a href="{{ route('stocks.index') }}" type="button" class="btn btn-danger mb-2 mr-2" style="float:left;margin-top:-37px;">
                                لیست کالاهای دست دوم
                           </a>
                           <hr>
                           <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>عنوان کالا</th>
                                    <th>توضیح</th>
                                    <th>دسته بندی</th>
                                    <th>برند</th>
                                    <th>تعداد</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->title }}</td>
                                        <td>{{ $stock->description }}</td>
                                        <td>
                                            @if (isset($stock->category->title))
                                                {{ $stock->category->title }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($stock->brand->title))
                                                {{ $stock->brand->title }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $stock->entity }}</td>
                                        <td>
                                            @if ($stock->isActive == 1 )
                                                <div class='badge badge-success'>فعال</div>
                                            @else
                                                <div class='badge badge-danger'>غیرفعال</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('stocks.restore',$stock->id) }}" method="POST" onsubmit="return confirm('آیا از بازگردانی رکورد مورد نظر اطمینان دارید؟');">
                                                @csrf
                                                <button type="submit" style="font-size:20px;border:none;background-color:transparent;float:right">
                                                    <i class="fa fa-refresh" style="color:#04a9f5;"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('stocks.trashed.post',$stock->id) }}" method="POST" onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                @method('delete')
                                                @csrf
                                                <button type="submit" style="font-size:20px;border:none;background-color:transparent;float:right">
                                                    <i class="fa fa-trash" style="color:#dc3545;"></i>
                                                </button>
                                            </form>
                                    </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

        </div>
    </div>
</div>



@endsection
