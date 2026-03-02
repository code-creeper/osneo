@props([
   'folders',
   'appliedFilters',
])

<ul>
    @foreach($folders as $folder => $subFolders)
        <x-tree-item
            :folder="$folder"
            :sub-folders="$subFolders"
            :filters="[]"
            :applied-filters="$appliedFilters"
            :level="0"
        />
    @endforeach
</ul>
