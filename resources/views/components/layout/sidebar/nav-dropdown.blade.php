@props([
    'label' => '',
    'icon' => 'fa fa-circle gray3',
    'id',
    'permission' => null,
    'permissionParams' => null,
    'arrow' => true,
    'level' => 2,
    'badge' => 'success',
    'badgeText' => false,
])

@php
    $levels = [
        2 => 'second',
        3 => 'third',
        4 => 'fourth'
    ];
    $level = $levels[$level] ?? 'second';

    $permission = $permission ? explode('|', $permission) : null;
@endphp

@if($permission == null || auth()->user()->canAny($permission, $permissionParams) )
<li class="side-nav-item">
    <a data-bs-toggle="collapse" class="side-nav-link"
       href="#{{$id}}" aria-expanded="false" aria-controls="{{$id}}">
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        @if($badgeText)
            <span class="badge bg-{{ $badge }} float-end">{{ $badgeText }}</span>
        @endif
        <span> {{ __($label) }}</span>

        @if($arrow)
            <span class="menu-arrow"></span>
        @endif
    </a>

    <div class="collapse" id="{{ $id }}">
        <ul class="side-nav-{{ $level }}-level" aria-expanded="false">
            {{ $slot }}
        </ul>
    </div>
</li>
@endif
