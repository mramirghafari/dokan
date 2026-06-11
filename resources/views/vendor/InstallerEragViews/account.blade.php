@section('title', 'ثبت لایسنس')
@extends('vendor.InstallerEragViews.app-layout')
@section('content')
    <section class="mt-4">
        <div class="container">
            <div class="col-md-6 cs_center">
                <form action="{{ route('databaseSeeder') }}" method="post" class="card">
                    {{-- <form action="{{ route('saveAccount') }}" method="post" class="card"> --}}
                    @csrf
                    <div class="card-body">
                        <div class="tab">

                            {{-- <div class="col-md-12 mb-3">
                                <x-install-input label="شماره سفارش" required="ture" name="order_id" type="text"
                                    value="{{ old('order_id') }}" />
                                <x-install-error for="order_id" />
                            </div>

                            <div class="col-md-12 mb-3">
                                <x-install-input label="نام کاربری در راست چین" required="ture" name="username"
                                    type="text" value="{{ old('username') }}" />
                                <x-install-error for="username" />
                            </div>

                            <div class="col-md-12 mb-3">
                                <x-install-input label="نام دامنه" required="ture" name="domain" type="text"
                                    value="{{ old('domain') }}" />
                                <x-install-error for="domain" />
                            </div> --}}

                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <div class="d-flex">
                            <button type="submit" id="next_button" class="btn btn-primary ms-auto">ثبت اطلاعات پایه در دیتابیس</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
@endsection
