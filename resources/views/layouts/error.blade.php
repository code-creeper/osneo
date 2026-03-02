<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="light-style"/>
    <link href="{{ asset('assets/css/app-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style"/>

    <link href="{{ asset('vendor/toastr/toastr.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" type="text/css"/>

    @livewireStyles
</head>

<body class="loading"
      data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
<div class="wrapper">
    @includeWhen(auth()->check(), 'includes.partial.sidebar')
    <div class="{{ auth()->check() ? 'content-page' : '' }}">
        <div class="content">
            <livewire:navbar/>
            <div class="container-fluid">
                {{--@include('includes.partial.heading')--}}
                <div class="row mt-4">
                    <div class="col-12">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        @includeWhen(auth()->check(), 'includes.partial.footer')
    </div>
</div>

<!-- bundle -->
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

@yield('scripts')
@livewireScripts

</body>
</html>
