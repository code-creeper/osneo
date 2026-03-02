@props([
    'fullscreen' => false
])
<div
    @class([
        'd-none',
        'overlay' => !$fullscreen,
        'overlay-fullscreen' => $fullscreen
    ])
    wire:loading.class.remove="d-none"
    x-init="$el.parentElement.style.position = 'relative'">
</div>
