<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="header-title mb-0">{{ __('Today') }}</h4>
        <div>
            <button class="btn btn-primary btn-sm"
                    wire:modal="forms.attendance-form, @js([
                                'userId' => auth()->id(), 'date' => now()->toDateString(), 'hiddenFields' => ['date', 'user_id']
                            ])">
                <i class="fal fa-plus"></i> Add
            </button>
        </div>
    </div>
    @if(!$user->attendances->count() && !$user->attendanceHasStarted() && !$user->onLeave())
        <div class="card-body bg-warning-lighten">
            <div class="text-center">
                <span><i class="fal fa-exclamation-circle fs-1 mb-2 text-warning"></i></span>
                <p>{{ __('Your attendance have not started') }}</p>
            </div>
        </div>
    @endif

    @if($user->onLeave() && !$user->attendances->count())
        <div class="card-body bg-success-lighten">
            <div class="text-center">
                <span><i class="fal fa-glass-cheers fs-1 mb-2 text-success"></i></span>
                <h5>{{ __('Out Of Office') }}</h5>
                <p>{{ __('Enjoy your day!') }}</p>
            </div>
        </div>
    @endif

    @if($user->attendances->count())
        <div class="card-body pt-0">
            <table class="table">
                @foreach($user->attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->checkin->format('H:i') }} - {{ $attendance->checkout?->format('H:i') }}</td>
                        <th class="text-end">{{ formatMins($attendance->duration, true) }}</th>
                    </tr>
                @endforeach
            </table>
            <div class="text-center">
                <a href="{{ route('attendances.index') }}" class="btn btn-success btn-sm">{{ __('View All') }}</a>
            </div>
        </div>
    @endif
</div>
