<div class="card">
    <x-loader/>
    <div class="card-header d-flex flex-column">
        <div class="d-flex mb-2">
            <h5 class="me-auto">{{ __('Files Manager') }}</h5>
        </div>

        @if(!$showInbox)
            <div class="d-flex g-2 row">

                @can('view all documents')
                    @wire('live')
                    <x-form-select2
                            wrapper-class="col-3" label="Ticket" name="ticketId"
                            class="form-control-sm" source="tickets" placeholder="Select Ticket"
                    />
                    @endwire
                @endcan

                <div class="align-items-end col-3 d-flex">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="assigned" wire:model.live="selfAssigned">
                        <label class="form-check-label" for="assigned">{{ __('Only show documents assigned to me') }}</label>
                    </div>
                </div>

                <div class="align-self-end ms-2 col">
                    <button class="btn btn-primary btn-sm" wire:click="resetFilters">{{ __('Reset') }}</button>
                </div>
            </div>
        @endif
    </div>
    <div class="card-body p-0 px-2">
        <div class="row">
            <div class="col-3">
                <div id="folders_tree" class="mt-2" wire:ignore>
                    <x-tree :folders="$folders" :applied-filters="$filters" wire:model.live="folder"/>
                </div>
            </div>
            <div class="col-9 table-responsive" style="min-height: 30rem;">
                <div class="mb-3">
                    <table class="table table-sm  table-hover fs--1">
                        <thead class="bg-200 text-900">
                        <tr>
                            <th></th>
                            <th>{{ __('File') }}</th>
                            @forelse($documentProperties as $documentProperty)
                                <th>{{ $documentProperty->name }}</th>
                            @empty
                            @endforelse
                            <th>{{ __('Assigned Users') }}</th>
                            <th>{{ __('Uploaded By') }}</th>
                            @if(!$showInbox)
                                <th>{{ __('Sorted By') }}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="list">
                        @foreach($documents as $document)
                            <tr>
                                <td>
                                    {!! $menu->getContents($document) !!}
                                </td>

                                <td>{{ str($document->name()) }}</td>
                                @forelse($documentProperties as $documentProperty)
                                    <td>{{ $document->properties->get("{$documentProperty->id}.value") }}</td>
                                @empty
                                @endforelse
                                <td>
                                    @foreach($document->users as $user)
                                        <span class="badge badge-success-lighten">{{ $user->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span>{{ $document->uploader->name }}</span>
                                    <span class="text-muted small d-block">
                                        <i class="fal fa-clock"></i>
                                        {{ $document->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                @if(!$showInbox)
                                    <td>
                                        <span>{{ $document->sorter?->name }}</span>
                                        <span class="text-muted small d-block">
                                            <i class="fal fa-clock"></i>
                                            {{ $document->sorted_on?->format('d-m-Y') }}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer py-0 border-top-0">
        <div class="mt-1 d-flex justify-content-between" style="line-height: 1;">
            <p class="fs--1 align-self-center">
                <select wire:model.live="perPage" class="form-control form-control-sm d-inline" style="width: auto"
                        id="per_page">
                    @foreach($perPageOptions as $option)
                        <option value="{{$option}}">{{$option}}</option>
                    @endforeach
                </select>
                <span>{{ __('Showing :firstItem to :lastItem of :total entries', ['firstItem' => $documents->firstItem(), 'lastItem' => $documents->lastItem(), 'total' => $documents->total()]) }}</span>
            </p>
            {{ $documents->appends(request()->query())->links() }}
        </div>
    </div>
</div>
