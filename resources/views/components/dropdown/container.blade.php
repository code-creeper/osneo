@props([
    'permission' => null,
    'permissionParams' => null,
])

@php
    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || user()->canAny($permission, $permissionParams) )
<div class="dropdown">
    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="far fa-ellipsis-h"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-end py-0" style="z-index: 10">
        {{ $slot }}
    </div>
</div>
@endif
