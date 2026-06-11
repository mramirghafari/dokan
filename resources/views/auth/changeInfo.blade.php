@extends('layouts.master')
@section('title', 'ویرایش اطلاعات')

@section('content')
    <div class="main-content">
        <nav aria-label="خرده نان" class="container-fluid">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">پیشخوان</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش اطلاعات کاربری</li>
            </ol>
        </nav>
        <div class="data-table-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12 box-margin height-card">
                        <div class="card card-body">
                            <h4 class="card-title">ویرایش</h4>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <form action="{{ route('profile.change.post') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-12">
                                                <label for="exampleInputEmail111">نام و نام خانوادگی:</label>
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ $user->name }}" required id="exampleInputEmail111">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="exampleInputEmail111">رمز عبور:</label>
                                                <input type="password" class="form-control" name="password"
                                                    id="exampleInputEmail111">
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="exampleInputEmail111">تکرار رمز عبور:</label>
                                                <input type="password" class="form-control" name="password_confirmation"
                                                    id="exampleInputEmail111">
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-outline-success mb-2 mr-2"
                                            style="float:left;"><i class="fa fa-save"></i> ذخیره</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- end row-->

            </div>
        </div>
    </div>



@endsection
