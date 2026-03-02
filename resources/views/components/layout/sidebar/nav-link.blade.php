@props([
    'label',
    'icon' => '',
    'route' => 'javascript: void(0);',
    'badge' => 'success',
    'badgeText' => false,
    'arrow' => false,
])

<a class="side-nav-link {{ request()->fullUrl() == $route ? 'active' : '' }}" href="{{ $route }}" {{ $attributes->merge() }}>
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

