<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<!-- App favicons -->
	@include('includes.partial.favicon')

    <!-- App css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />

    <link href="{{ asset('vendor/toastr/toastr.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('vendor/wire-elements-pro/css/bootstrap-overlay-component.css') }}">

</head>

<body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":{{ Cache::get("leftSidebarCondensed" . Auth::id(), false)  ? "true" : "false" }}, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}' id="draggable">
<div class="wrapper">

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row mt-4">
                    <div class="col-12">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>


<!-- bundle -->

<script src="{{ asset('assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>


@yield('scripts')

@livewire('modal-pro')

<script src="{{ asset('vendor/wire-elements-pro/js/overlay-component.js') }}"></script>
{{--<script src="{{ asset('vendor/wire-elements-pro/js/insert-component.js') }}"></script>--}}
</body>
</html>
