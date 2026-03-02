@props([
    'label' => '',
    'permission' => null,
    'permissionParams' => null,
    'route' => 'javascript: void(0);',
    'target' => '_self'
])

@php
    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || auth()->user()->canAny($permission, $permissionParams) )
<li {{ $attributes->merge() }}>
    <a href="{{ $route }}" target="{{$target}}">{{ __($label) }}</a>
</li>
@endif
