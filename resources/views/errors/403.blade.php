<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Title -->
    <title>خطای 403</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/core-img/favicon.png') }}">

<link rel="stylesheet" href="{{ asset('style.css') }}">

</head>

<body>

<!-- Error Page Area -->
<div class="error-page-area">
    <!-- Error Content -->
    <div class="error-content text-center">
        <!-- Error Thumb -->
        <div class="error-thumb">
            <img src="{{ asset('img/bg-img/user-access.png') }}" alt="">
        </div>
        <h2>اوه! شما به این صفحه دسترسی ندارید!</h2>
        <p>به مدیر سیستم اطلاع دهید</p>
        <a class="btn btn-rounded btn-primary mt-30" href="/deliveries">بازگشت</a>
    </div>
</div>

<!-- Must needed plugins to the run this template -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bundle.js') }}"></script>

<!-- Active JS -->
<script src="{{ asset('js/default-assets/active.js') }}"></script>

</body>
</html>
