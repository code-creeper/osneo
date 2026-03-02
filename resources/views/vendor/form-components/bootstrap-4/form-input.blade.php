@props([
    'insert' => null
])

<div class="@if($type === 'hidden') d-none @else form-group @endif {{ $wrapperClass }}">
    <x-form-label :label="$label" :for="$attributes->get('id') ?: $id()" />

    <div class="input-group">
        @isset($prepend)
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {!! $prepend !!}
                </div>
            </div>
        @endisset

        <input {!! $attributes->merge(['class' => 'form-control ' . ($hasError($name) ? 'is-invalid' : '')]) !!}
            type="{{ $type }}"

            @if($isWired())
                wire:model{!! $wireModifier() !!}="{{ $dottedNotationName() }}"
            @else
                value="{{ $value }}"
            @endif

            name="{{ $name }}"

            @if($insert) {{ wep_insert([$insert]) }} @endif

            @if($label && !$attributes->get('id'))
                id="{{ $id() }}"
            @endif
        />

        @isset($append)
            <div class="input-group-append">
                <div {{ $append->attributes->class(['input-group-text h-100 rounded-0 rounded-end']) }}>
                    {!! $append !!}
                </div>
            </div>
        @endisset

        @if($hasErrorAndShow($name))
            <x-form-errors :name="$name" />
        @endif
    </div>

    {!! $help ?? null !!}

</div>
