@props([
'folder',
'subFolders',
'filters',
'appliedFilters',
'level'
])

@php
    $filterNames = ['folder', 'source', 'doc_type', 'year', 'month'];
    $currentFilter = $filterNames[$level];
    $filters = array_merge($filters, [$currentFilter => $folder]);
@endphp

<li
        data-filters="{{ json_encode($filters) }}"
        data-jstree="{{ prepareTreeData($folder, $currentFilter, $appliedFilters, $filters) }}"
>
    {{ $folder }}
    @if (is_array($subFolders))
        <ul>
            @foreach($subFolders as $folder => $subSubFolders)
                <x-tree-item
                        :folder="$folder ?: 'Null'"
                        :sub-folders="$subSubFolders"
                        :filters="$filters"
                        :applied-filters="$appliedFilters"
                        :level="$level + 1"
                />
            @endforeach
        </ul>
    @endif
</li>
