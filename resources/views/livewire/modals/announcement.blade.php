<x-wire-elements-pro::bootstrap.modal>
    @php($current = $index+1)
    <x-slot name="title">
        <i class="fad fa-megaphone text-primary me-1"></i>

        @if(!$preview)
            <strong class="text-info">{{ __('Announcement :current:', ['current' => "$current/$total" ]) }}</strong>
        @endif

        {{ $announcement->subject }}
    </x-slot>

    <p class="my-0">{!! $announcement->body !!}</p>

    <div class="d-flex justify-content-between border-top pt-2 mt-2">
        @if($preview)
            <div></div>
            <div>
                <button class="btn btn-sm btn-primary" type="button"
                        wire:modal="forms.announcement-form, @js(['announcement' => $announcement->id])"
                >{{ __('Edit') }}</button>
                <button class="btn btn-sm btn-danger" type="button"
                        wire:click="$dispatch('delete-announcement', { announcement: {{ $announcement->id }} })"
                >{{ __('Delete') }}</button>
            </div>
        @else
            <div>
                @if($current > 1)
                    <button class="btn btn-sm btn-outline-primary" type="button"
                            wire:click="previous">{{ __('Previous') }}</button>
                @endif

                @if($current < $total)
                    <button class="btn btn-sm btn-outline-primary" type="button"
                            wire:click="next">{{ __('Next') }}</button>
                @endif
            </div>
            <div>
                @if($current == $total)
                    <button class="btn btn-sm btn-success" type="button"
                            wire:click="markAllAsRead">{{ __('Mark All As Read') }}</button>
                @endif
                <button class="btn btn-sm btn-success" type="button"
                        wire:click="markAsRead">{{ __('Mark as Read') }}</button>
            </div>
        @endif
    </div>
</x-wire-elements-pro::bootstrap.modal>
