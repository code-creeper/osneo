@props([
    'logo_small' => asset('images/logo/logo_small.png'),
    'logo' => '',
    'mode' => 'light'
])

<a href="{{ route('dashboard') }}" class="logo text-center logo-{{ $mode }}">
    <span class="logo-lg">
        <img src="{{ $logo }}" alt="" height="40">
    </span>
    <span class="logo-sm">
        <img src="{{ $logo_small }}" alt="" height="40">
    </span>
</a>
