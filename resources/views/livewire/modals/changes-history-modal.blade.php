<x-wire-elements-pro::bootstrap.slide-over>
    <div data-simplebar>
        <div class="timeline-alt pb-0">
            @foreach($activities as $activity)
                <div class="timeline-item">
                    <i class="{{ $activity->getIcon() }} timeline-icon"></i>
                    <div class="timeline-item-info">
                        <a href="#" class="text-info font-weight-bold mb-1 d-block">{{ $activity->causer->name }}</a>
                        @foreach($activity->getFormattedChanges() as $change)
                            <small>{!! $change !!} </small> <br>
                        @endforeach
                        <p class="mb-0 pb-2">
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-wire-elements-pro::bootstrap.slide-over>
