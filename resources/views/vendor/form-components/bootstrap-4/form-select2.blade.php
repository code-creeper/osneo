<div class="form-group {{ $wrapperClass }}" x-ref="select2Container" wire:ignore

     x-modelable="value"

     {{--todo:: if morphr markers are enabled, this is messed up somehow --}}
     {{--@if($isWired())--}}
        wire:model{!! $wireModifier() !!}="{{ $dottedNotationName() }}"
     {{--@endif--}}

     x-data="{
        config: @js($config),
        value: null,
        initialized: false,

        init() {
            this.value = this.config.multiple ? [] : '';

            this.config.dropdownParent = this.getDropdownParent(this.config.dropdownParent);

            let bootSelect2 = () => {
                let selections = this.config.multiple ? this.value : [this.value];

                // convert ids to string for easy comparison
                selections = selections.map(i => String(i));

                if (this.config.data !== undefined){
                    this.config.data = this.config.data.map(i => ({
                        id: i.id,
                        text: i.text,
                        selected: selections.includes(String(i.id)),
                    }));
                }

                $(this.$refs.select).select2(this.config)
            }

            let refreshSelect2 = () => {
                if (this.initialized){
                    return;
                }

                this.initialized = true;

                if (this.config.selectedOptions !== undefined){
                    this.config.selectedOptions
                    .map(option => this.$refs.select.innerHTML = `<option value='${option.id}' selected='selected'>${option.text}</option>`)
                    .join('')
                }

                $(this.$refs.select).val(this.value).trigger('change')
            }

            bootSelect2()

            $(this.$refs.select).on('change', () => {
                let currentSelection = $(this.$refs.select).select2('data')

                this.value = this.config.multiple
                    ? currentSelection.map(i => i.id)
                    : currentSelection[0].id;
            })

            this.$watch('value', () => refreshSelect2())

            document.addEventListener('updateSelect2', (e) => {
                console.log('updating select2...');
                //TODO
            })
        },

        getDropdownParent(dropdownParent){
            // If dropdownParent is not defined and the element has a form parent, set it to the form
            if (dropdownParent === undefined && $($el).closest('form').length) {
                return $($el).closest('form');
            }

            // If dropdownParent is defined, evaluate it
            if (dropdownParent !== undefined) {
                return $(dropdownParent)
            }

            // If dropdownParent is still not defined, set it to the body
            return $('body');
        }
    }"
>
    <x-form-label :label="$label" :for="$attributes->get('id') ?: $id()" />

    <select
            x-ref="select"

            name="{{ $name }}"

            @if($label && !$attributes->get('id'))
                id="{{ $id() }}"
            @endif

            {!! $attributes->merge(['class' => 'form-control ' . ($hasError($name) ? 'is-invalid' : '')]) !!}>
        @if(!$multiple)
            <option></option>
        @endif
    </select>

    {!! $help ?? null !!}

    @if($hasErrorAndShow($name))
        <x-form-errors :name="$name" />
    @endif
</div>
