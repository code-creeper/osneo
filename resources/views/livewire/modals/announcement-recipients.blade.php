<x-wire-elements-pro::bootstrap.slide-over>
    <x-slot name="title">{{ __('Announcement Recipients') }}</x-slot>

    <div class="row g-2">
        <div class="col-12">
            <ul class="list-group list-group-flush">
                @foreach($announcement->users->sortByDesc('pivot.read_at') as $user)
                    <li class="d-flex justify-content-between list-group-item px-0">
                        <span>{{ $user->name }} </span>
                        <span style="font-size: 12px">
                            <i class="fal fa-clock"></i>
                            {{ $user->pivot->read_at ? $user->pivot->read_at : __('Not Read') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-wire-elements-pro::bootstrap.slide-over>
