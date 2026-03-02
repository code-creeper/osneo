@props([
    'id',
    'active' => false,
    'label',
    'permission' => null,
    'permissionParams' => null,
])

@php
    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || user()->canAny($permission, $permissionParams) )
    <li class="nav-item" wire:ignore>
        <a class="nav-link {{ $active ? 'show active' : '' }}" id="{{$id}}-tab" data-bs-toggle="tab" href="#tab-{{$id}}"
           role="tab" aria-controls="tab-{{$id}}" aria-selected="true">
            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                {{ __($label) }}
            @endif
        </a>
    </li>
@endif
