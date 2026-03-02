<!DOCTYPE html>
<html lang="en" >
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

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@ryangjchandler/alpine-clipboard@2.x.x/dist/alpine-clipboard.js" defer></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/de.js"></script>

    <link rel="stylesheet" href="{{ asset('vendor/wire-elements-pro/css/bootstrap-insert-component.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/wire-elements-pro/css/bootstrap-overlay-component.css') }}">

    <link href="{{ asset('css/media-library.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/skeleton-loader.css') }}" rel="stylesheet" type="text/css"/>


    @yield('styles')
    @livewireStyles
    @mediaLibraryStyles
</head>

<body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":{{ Cache::get("leftSidebarCondensed" . Auth::id(), false)  ? "true" : "false" }}, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}' id="draggable">
<div class="wrapper">
    <livewire:layout.sidebar/>

    <div class="content-page">
        <div class="content">
            <livewire:navbar/>
            <div class="container-fluid">
                <div class="row mt-4">
                    <div class="col-12">
						<livewire:attendance-alert/>
                        {{ $slot ?? null }}
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        @include('includes.partial.footer')

    </div>
</div>

{{--@include('includes.partial.settings')--}}


<!-- bundle -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>

<script src="{{ asset('vendor/toastr/toastr.min.js') }}"></script>
<script src="https://unpkg.com/imask"></script>

<script>
    @php($notifications = array('error', 'success', 'warning', 'info'))
    @foreach($notifications as $notification)
        @if(session()->has($notification))
            @php($message = session()->get($notification))
			$.NotificationApp.send("{{ __(ucfirst($notification)) }}","{{ __($message) }}","top-right","rgba(0,0,0,0.2)","{{ $notification }}");
        @endif
    @endforeach
</script>

@yield('scripts')

@livewire('insert-pro')
@livewire('slide-over-pro')
@livewire('modal-pro')
@livewireScripts
@mediaLibraryScripts

@include('includes.partial.livewire-scripts')

<script src="{{ asset('vendor/wire-elements-pro/js/overlay-component.js') }}"></script>
<script src="{{ asset('vendor/wire-elements-pro/js/insert-component.js') }}"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/languages/sql.min.js"></script>
</body>
</html>
