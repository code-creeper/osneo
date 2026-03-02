@php
    $toolbar = [
          [[ 'header'=> [1, 2, 3, 4, 5, 6, false] ]],
          [[ 'font'=> [] ]],
          ['bold', 'italic', 'underline'],
          ['blockquote', 'link'],

          [[ 'list'=> 'ordered'], [ 'list'=> 'bullet' ]],
          [[ 'indent'=> '-1'], [ 'indent'=> '+1' ]],

          [[ 'color'=> [] ], [ 'background'=> [] ]],
          [[ 'align'=> [] ]],
          ['clean'],
    ];
@endphp

<div
    {{ $attributes->merge() }}
    wire:ignore
    x-modelable="value"
    x-data="{
        value: $el.value,
        init() {
            let quill = new Quill(this.$refs.quill, {
                modules: {
                    toolbar: {{ json_encode($toolbar) }}
                },
                theme: 'snow'
            })

            quill.root.innerHTML = this.value

            quill.on('text-change', () => {
                this.value = quill.root.innerHTML
            })
        },
    }"
>
    <div x-ref="quill" style="height: 50vh" ></div>
</div>
