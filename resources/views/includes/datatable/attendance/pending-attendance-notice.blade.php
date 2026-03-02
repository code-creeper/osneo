@if($pendingAttendanceCount)
    <div class="alert alert-info" role="alert">
        <i class="dripicons-information me-2"></i>
        <span>
            {{ __('You have :count :attendance pending for approval.', [
            'count' => $pendingAttendanceCount,
            'attendance' => __(str('attendance')->plural($pendingAttendanceCount)->value())
            ]) }}
        </span>
        @if(user()->can('view own modifications'))
            <a href="{{ route('modifications.index') }}">{{ __('See details') }}</a>
        @endif
    </div>
@endif
