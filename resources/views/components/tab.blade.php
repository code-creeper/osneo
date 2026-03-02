@props([
    'id' => 'tab',
    'bordered' => true,
    'card' => false,
    'justified' => false,
    'tabClasses' => '',
    'tabContentClasses' => '',
    'paddingX',
])

@php
    $tabClasses .= $bordered ? ' nav-bordered' : '';
    $tabClasses .= $justified ? ' nav-justified' : '';
    $padding = 'p-3 ';
    $padding .= isset($paddingX) ? "px-{$paddingX}" : '';
@endphp

@if($card)
    <div class="card">
        <div class="card-header border-0 px-0 pb-0">
            @endif
            <ul class="nav nav-tabs {{ $tabClasses }}" id="{{$id}}" role="tablist">
                {{ $tabs }}
            </ul>
            @if($card)</div> @endif
        @if($card)
            <div class="card-body p-0">
                @endif
                <div class="tab-content {{ $card ? '' : $padding }} {{ $tabContentClasses }}" id="{{$id}}Content">
                    {{ $slot }}
                </div>
                @if($card)
            </div>
    </div>
@endif

