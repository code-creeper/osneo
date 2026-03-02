<div>
    <x-livewire-tables::tools.filter-label :$filter :$filterLayout :$tableName :$isTailwind :$isBootstrap4 :$isBootstrap5 :$isBootstrap />

    <div @class([
        'rounded-md shadow-sm' => $isTailwind,
        'inline' => $isBootstrap,
    ])>
        @wire('live')
        <x-form-select2
            name="filterComponents.{{ $filter->getKey() }}"
            id="{{ $tableName }}-filter-{{ $filter->getKey() }}"
            :options="$filter->getOptions()" placeholder="Select"
        />
        @endwire
    </div>
</div>
