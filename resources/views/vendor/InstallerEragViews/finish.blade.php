@section('title', 'پایان')
@extends('vendor.InstallerEragViews.app-layout')
@section('content')
    <section class="mt-4">
        <div class="container">
            <div class="row justify-content-center">
                <i class="bi bi-check2-circle text-center"></i>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <h5 class="purple-text text-center finish">اسکریپت با موفقیت نصب شد.
                        
                        <a href="{{ route('finishSave') }}">برای اتمام کلیک کنید</a>
                    </h5>

                    اطلاعات کاربری مدیر:
                    نام کاربری: admin@almas.com
                    رمز عبور: admin
                </div>
            </div>
        </div>
    </section>
@endsection
