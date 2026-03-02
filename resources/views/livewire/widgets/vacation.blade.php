<div @class([
        'card',
        'shadow-none' => !$shadow
])>
    <x-loader/>
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="header-title mb-0">{{ __('Vacations') }}</h4>
        <div>
            <select class="form-select form-select-sm" wire:model.live="vacationYear">
                @foreach($vacationYears as $year => $yearName)
                    <option>{{ $yearName }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table">
            <tr>
                <td>{{ __('Previous Year') }}</td>
                <th class="text-end">{{ __(':count Days', ['count' => $vacationLastYear]) }}</th>
            </tr>
            <tr>
                <td>{{ __('Current Year') }}</td>
                <th class="text-end">{{ __(':count Days', ['count' => $vacationThisYear]) }}</th>
            </tr>
            <tr>
                <td>{{ __('Taken') }}</td>
                <th class="text-end">{{ __(':count Days', ['count' => $leaveStats->leaves_taken ?? 0]) }}</th>
            </tr>
            <tr>
                <td>{{ __('Planned') }}</td>
                <th class="text-end">
                    @if($leaveStats->leaves_pending)
                        <span class="badge badge-outline-info">
                            {{ __(':count days pending', ['count' => $leaveStats->leaves_pending]) }}
                        </span>
                    @endif
                    {{ __(':count Days', ['count' => $leaveStats->leaves_planned ?? 0]) }}
                </th>
            </tr>
            <tr>
                <td>{{ __('Open') }}</td>
                <th class="text-end">{{ __(':count Days', ['count' => $leavesBalance]) }}</th>
            </tr>
        </table>
    </div>
</div>
