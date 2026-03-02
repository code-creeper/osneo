@props([
    'property'
])

@php
use \App\Enums\DocumentPropertyType
@endphp

@if(in_array($property->type, [DocumentPropertyType::Ticket, /*DocumentPropertyType::CONTACT_NAME*/]))
    <x-form-input
            name="properties.{{$property->name}}" :label="$property->name"
            :insert="$property->type == DocumentPropertyType::Ticket ? 'ticket' : 'contact'"
    />

@elseif($property->type == DocumentPropertyType::DATE)
    <x-form-flatpickr name="properties.{{$property->name}}" :label="$property->name"/>
@else
    <x-form-input
        name="properties.{{$property->name}}" :label="$property->name" step=".01"
        :type="$property->type == DocumentPropertyType::INTEGER ? 'number' : 'text'"
    />
@endif
