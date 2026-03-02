<div @class([
        'card',
        'shadow-none' => !$shadow
])>
    <x-loader/>

    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="header-title mb-0">{{ __('Payroll') }}</h4>
        <x-form-input type="month" name="payrollMonth" wire:model.live="payrollMonth"/>
    </div>
    <div class="card-body pt-0">
        @if($employmentNotFound)
            <div class="mt-5 text-center">
                <h4>{{ __('No employment found for this month') }}</h4>
            </div>
        @else
            <table class="table">
                <tr>
                    <td>{{ __('Attendance') }}</td>
                    <th class="text-end">{{ formatMins($payroll->attendance, true) }}</th>
                </tr>
                <tr>
                    <td>{{ __('Leaves') }}</td>
                    <th class="text-end">
                        @if($payroll->leaves)
                            <span class="badge badge-outline-success mr-2">
                                {{ __(':count Days', ['count' => $payroll->leaves]) }}
                            </span>
                        @endif
                        <span>{{ formatMins($payroll->leaves_hours, true) }}</span>
                    </th>
                </tr>

                @if($payroll->holiday_hours)
                    <tr>
                        <td>{{ __('Holidays') }}</td>
                        <th class="text-end">
                            <span class="badge badge-outline-success mr-2">
                                {{ __(':count Days', ['count' => $payroll->holidays]) }}
                            </span>
                            <span>{{ formatMins($payroll->holiday_hours, true) }}</span>
                        </th>
                    </tr>
                @endif
                <tr>
                    <td>{{ __('Target Hours') }}</td>
                    <th class="text-end">{{ formatMins($payroll->target_hours, true) }}</th>
                </tr>
                <tr>
                    <td>{{ __('Overtime') }}</td>
                    <th class="text-end">{{ formatMins($payroll->overtime, true) }}</th>
                </tr>
                <tr>
                    <td>{{ __('Payout') }}</td>
                    <th class="text-end">{{ formatMins($payroll->payout, true) }}</th>
                </tr>
                <tr>
                    <td>{{ __('Balance') }}</td>
                    <th class="text-end">
                        @if($payroll->current_month_balance)
                            <span class="badge badge-outline-success mr-2">
                                {{ formatMins($payroll->current_month_balance, true) }}
                            </span>
                        @endif
                        <span>{{ formatMins($payroll->total_balance, true) }}</span>
                    </th>
                </tr>
            </table>
        @endif
    </div>
</div>
