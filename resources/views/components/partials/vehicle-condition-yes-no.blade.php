@props([
    'wrapperClass' => 'col',
    'label',
    'field'
])

<x-form-group class="{{ $wrapperClass }}" :label="$label" inline>
    <x-form-radio name="{{ $field }}" value="yes" label="Yes"/>
    <x-form-radio name="{{$field}}" value="no" label="No"/>
</x-form-group>
