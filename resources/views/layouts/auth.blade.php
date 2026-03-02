<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('vendor/toastr/toastr.min.css') }}" rel="stylesheet"/>

</head>

<body class="loading authentication-bg" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">

                    <!-- Logo -->
                    <div class="card-header pt-4 pb-4 text-center bg-primary">
                        <a href="{{ route('dashboard') }}">
                            <span><img src="{{ asset('images/logo/logo_light.png') }}" alt="" height="45"></span>
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center w-75 m-auto">
                            <h4 class="text-dark-50 text-center pb-0 fw-bold">@yield('heading')</h4>
                            <p class="text-muted mb-4">@yield('sub-heading')</p>
                        </div>
                        @yield('content')
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p class="text-muted">@yield('footer')</p>
                    </div>
                </div>

            </div>
        </div>
        <!-- end row -->
    </div>
</div>

<script src="{{ asset('assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>

<script src="{{ asset('vendor/toastr/toastr.min.js') }}"></script>
<script>
    @php($notifications = array('error', 'success', 'warning', 'info'))
    @foreach($notifications as $notification)
        @if(session()->has($notification))
            @php($message = session()->get($notification))
            {{ "toastr." . $notification }}{!! "('" !!}{{ __($message) }}{!! "')" !!}
        @endif
    @endforeach
</script>

</body>
</html>
