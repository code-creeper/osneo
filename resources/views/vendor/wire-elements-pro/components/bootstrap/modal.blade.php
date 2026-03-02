@props([
    'contentPadding' => true,
    'onSubmit' => null,
    'closeIcon' => true,
    'headless' => false,
    'loader' => true,
    'title' => false,
])

<form wire:submit.prevent="{{ $onSubmit }}" {{ $attributes->merge() }}>

    @if($loader)
        <x-loader/>
    @endif

    @if(!$headless)
        <div class="modal-header">
            @if($title ?? false)
                <h5 class="modal-title">{{ $title }}</h5>
            @endif

            @if('closeIcon')
                <button type="button" class="btn-close" wire:modal="close" aria-label="Close"></button>
            @endif
        </div>
    @endif

    <div @class(['modal-body' , 'px-0 py-0' => !$contentPadding])>
        {{ $slot }}
    </div>
    @if($buttons ?? false)
        <div class="modal-footer">
            {{ $buttons }}
        </div>
    @endif
</form>
