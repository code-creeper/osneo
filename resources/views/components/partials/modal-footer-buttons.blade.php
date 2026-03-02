@props([
    'submitText' => 'Save Changes',
    'cancelText' => 'Cancel'
])

<x-slot name="buttons">
    <button class="btn btn-sm btn-primary" type="button" wire:modal="close">{{ __($cancelText) }}</button>
    <button class="btn btn-sm btn-success" type="submit">{{ __($submitText) }}</button>
</x-slot>
