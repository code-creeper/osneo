@props([
    'label' => '',
    'icon' => '',
    'route' => 'javascript: void(0);',
    'permission' => null,
    'permissionParams' => null,
    'badge' => 'success',
    'badgeText' => false,
])

@php
    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || user()->canAny($permission, $permissionParams) )
<li class="side-nav-item">
    {{ $slot }}

    @if($label)
        <x-layout.sidebar.nav-link :icon="$icon" :label="$label" :route="$route" :badge="$badge" :badgeText="$badgeText" {{ $attributes->merge() }}/>
    @endif
</li>
@endif

