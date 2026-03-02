<div class="@if($type === 'hidden') d-none @else form-group @endif {{ $wrapperClass }}"

     x-modelable="value"

     @if($isWired())
         wire:model{!! $wireModifier() !!}="{{ $dottedNotationName() }}"
     @endif

     x-data="{
        value: null,
        init() {
            let config = @js($config);

            let picker = flatpickr(this.$refs.picker, config)

            this.$watch('value', () => picker.setDate(this.value))
        }
    }">
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

               x-ref="picker"

               @if($isWired())
                   wire:model{!! $wireModifier() !!}="{{ $dottedNotationName() }}"
               @else
                   value="{{ $value }}"
               @endif

               name="{{ $name }}"

               @if($label && !$attributes->get('id'))
                   id="{{ $id() }}"
               @endif
        />

        @isset($append)
            <div class="input-group-append">
                <div class="input-group-text">
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
