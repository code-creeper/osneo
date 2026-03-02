<div>
    @if(!user()->attendanceHasStarted())
        <div class="alert alert-warning" role="alert">
            <i class="dripicons-warning me-2"></i><strong>{{ __('Warning') }}!</strong> {{ __('Your attendance have not started.') }}
        </div>
    @endif
</div>
