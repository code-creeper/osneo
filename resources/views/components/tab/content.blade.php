@props([
    'id',
    'active' => false,
    'permission' => null,
    'permissionParams' => null,
])

@php
    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || user()->canAny($permission, $permissionParams) )
    <div class="tab-pane fade {{ $active ? 'show active' : '' }}" id="tab-{{$id}}" role="tabpanel"
         aria-labelledby="{{$id}}-tab" wire:ignore.self>
        {{ $slot }}
    </div>
@endif
