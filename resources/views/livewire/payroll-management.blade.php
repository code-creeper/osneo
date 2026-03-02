<div>
    <x-loader/>
    <div class="card">
        <div class="card-header">
            <h5>{{ __('Payroll') }} - {{ $user->name }}</h5>
        </div>
        <div class="card-body">
            <x-tab :padding-x="0" :justified="true">
                <x-slot name="tabs">
                    <x-tab.item label="Employment Details" id="pr-employment" :active="true"/>
                    <x-tab.item label="Time Issues" id="pr-time_issues"/>
                    <x-tab.item label="Abnormalities" id="pr-abnormalities"/>
                    <x-tab.item label="Payroll" id="pr-payroll"/>
                    <x-tab.item label="Preview" id="pr-preview"/>
                </x-slot>
                <x-tab.content id="pr-employment" :active="true">
                    <livewire:modals.employment-management :user="$user" :headless="true"/>
                </x-tab.content>
                <x-tab.content id="pr-time_issues">
                    <div class="row">
                        <div class="col-6">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Missing Times') }}</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th></th>
                                        </tr>
                                        @foreach($missing_times as $date)
                                            <tr>
                                                <td>{{ $date->format('d.m.Y') }}</td>
                                                <td class="text-end">
                                                    <a href="#"
                                                       wire:modal="forms.attendance-form, @js([
                                                            'userId' => $user->id,
                                                            'date' => $date->format('d.m.Y'),
                                                            'hiddenFields' => ['date', 'user_id']
                                                       ])">
                                                        {{ __('Add Attendance') }}
                                                    </a>
                                                    <span>|</span>
                                                    <a href="#"
                                                       wire:modal="forms.leave-form, @js([
                                                            'userId' => $user->id,
                                                            'dates' => $date->format('d.m.Y'),
                                                            'hiddenFields' => ['dates', 'user_id']
                                                       ])">
                                                        {{ __('Add Leave') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Forgotten Logouts') }}</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Checkin') }}</th>
                                        </tr>
                                        @foreach($forgotten_logouts as $attendance)
                                            <tr>
                                                <td>{{ $attendance->date->format('d.m.Y') }}</td>
                                                <td>{{ $attendance->checkin->format('H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <livewire:datatables.leave-datatable
                                :user="$user->id" status="pending"
                                :month="$payroll->date->month"
                                :year="$payroll->date->year"
                                :date="$payroll->date->format('Y-m')"
                            />
                        </div>
                    </div>
                </x-tab.content>
                <x-tab.content id="pr-abnormalities">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Unusual Attendances') }}</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Duration') }}</th>
                                            <th>{{ __('Anomaly') }}</th>
                                        </tr>

                                        @foreach($abnormalAttendances as $attendance)
                                            <tr>
                                                <td>{{ $attendance->date->format(config('dates.default')) }}</td>
                                                <td>{{ formatMins($attendance->working_time, true) }}</td>
                                                <td>{{ $attendance->anomaly }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <h4>{{ __('Changes From Previous Month') }}</h4>
                        <div class="col-6">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Attendance') }}</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Checkin') }}</th>
                                            <th>{{ __('Checkout') }}</th>
                                            <th>{{ __('Duration') }}</th>
                                            <th>{{ __('Updated On') }}</th>
                                        </tr>
                                        @foreach($updated_attendances as $attendance)
                                            <tr>
                                                <td>{{ $attendance->date->date() }}</td>
                                                <td>{{ $attendance->checkin->format('H:i') }}</td>
                                                <td>{{ $attendance->checkout->format('H:i') }}</td>
                                                <td>{{ $attendance->formatted_duration }}</td>
                                                <td>{{ $attendance->updated_at->date() }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Leave') }}</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Starts On') }}</th>
                                            <th>{{ __('Ends On') }}</th>
                                            <th>{{ __('Days') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                        </tr>
                                        @foreach($updated_leaves as $leave)
                                            <tr>
                                                <td>{{ $leave->starts_on->date() }}</td>
                                                <td>{{ $leave->ends_on->date() }}</td>
                                                <td>{{ $leave->days }}</td>
                                                <td>{{ $leave->reason->name }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-tab.content>

                <x-tab.content id="pr-payroll">
                    <x-errors/>
                    <div class="row g-2">
                        <div class="col-6">
                            <livewire:widgets.payroll
                                :user-id="$user->id"
                                :payroll-month="$payroll->month"
                                :shadow="false"
                            />
                        </div>
                        <div class="col-6">
                            <livewire:widgets.vacation
                                :user-id="$user->id"
                                :vacation-year="$payroll->date->year"
                                :shadow="false"
                            />
                        </div>

                        <x-form-textarea label="Notes" name="payroll.notes"/>

                        <div class="col-12">
                            <h5>{{ __('Overtime') }}</h5>
                        </div>

                        @foreach($overtimes as $index => $overtime)
                            <x-form-select
                                    label="Select Employment" placeholder="Select Employment"
                                    name="overtimes.{{$index}}.hourly_rate" wrapper-class="col-6"
                                    :options="$user->employments->toKeyValuePair('hourly_rate', 'period')"
                            />

                            <x-form-input wrapper-class="col-5" label="Hours" name="overtimes.{{$index}}.hours"/>

                            <div class="col-1 d-flex flex-column justify-content-end">
                                <button class="btn btn-danger" wire:click.prevent="removeOvertime({{ $index }})">
                                    <i class="fal fa-trash"></i>
                                </button>
                            </div>
                        @endforeach

                        <div class="col-12 text-end">
                            <button
                                class="btn btn-light btn-sm"
                                wire:click="addOvertime"
                                wire:loading.attr="disabled"
                                wire:target="addOvertime"
                            >{{ __('Add Overtime') }}
                            </button>
                        </div>

                        <div class="col-12"><h5>Surcharge</h5></div>
                        @foreach($surcharges as $index => $surcharge)
                            <x-form-input wrapper-class="col-3" name="surcharges.{{$index}}.description" label="Description"/>
                            <x-form-input wrapper-class="col-3" name="surcharges.{{$index}}.amount" label="Amount"/>

                            <x-form-select
                                    wrapper-class="col-5" name="surcharges.{{$index}}.tax" label="Tax"
                                    :options="['gross' => 'Gross', 'net' => 'Net']"
                            />

                            <div class="col-1 d-flex flex-column justify-content-end">
                                <button class="btn btn-danger" wire:click.prevent="removeSurcharge({{ $index }})">
                                    <i class="fal fa-trash"></i>
                                </button>
                            </div>
                        @endforeach

                        <div class="col-12 text-end">
                            <button
                                class="btn btn-light btn-sm"
                                wire:click="addSurcharge"
                                wire:loading.attr="disabled"
                                wire:target="addSurcharge"
                            >{{ __('Add Surcharge') }}
                            </button>
                        </div>

                        <x-form-input label="Vacation" name="payroll.leaves_balance"/>
                        <x-form-input label="Information" name="payroll.information"/>

                        <div class="col-12 text-end">
                            <button class="btn btn-success btn-sm" wire:click="submit">{{ __('Save') }}</button>
                        </div>
                    </div>
                </x-tab.content>

                <x-tab.content id="pr-preview">
                    <div class="row">
                        <div class="col-4">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Personal Information') }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Employee ID') }}</th>
                                            <td class="text-end">{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <td class="text-end">{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Address') }}</th>
                                            <td class="text-end">{{ $user->address }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Employment Period') }}</th>
                                            <td class="text-end">{{ $user->employment?->period }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Absences') }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Reason') }}</th>
                                            <th class="text-end">{{ __('This Month') }}</th>
                                            <th class="text-end">{{ __('This Year') }}</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($leavesByReason as $reason)
                                                <tr>
                                                    <th>{{ $reason['name'] }}</th>
                                                    <td class="text-end">{{ $reason['leaves_this_month'] }}</td>
                                                    <td class="text-end">{{ $reason['leaves_this_year'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Vacation') }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Previous Year') }}</th>
                                            <td class="text-end">{{ $vacation['last_year'] }} {{ __('Days') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('This Year') }}</th>
                                            <td class="text-end">{{ $vacation['this_year'] }} {{ __('Days') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Taken') }}</th>
                                            <td class="text-end">{{ $vacation['taken'] }} {{ __('Days') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Open') }}</th>
                                            <td class="text-end">{{ $vacation['current_balance'] }} {{ __('Days') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card shadow-none border">
                                <div class="card-header">
                                    <h4 class="text-center">{{ __('Summary') }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Qty') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th class="text-end">{{ __('Tax') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th>{{ __('Salary') }}</th>
                                            <td>{{ $payroll->working_hours ?? 0 }} h</td>
                                            <td>{{ money($payroll->salary, forceDecimals: true) }}</td>
                                            <td class="text-end">{{ __('Gross') }}</td>
                                        </tr>

                                        @foreach($payroll->overtimes as $overtime)
                                        <tr>
                                            <th>
                                                {{ __('Overtime') }}
                                            </th>
                                            <td>{{ $overtime['hours'] ?? 0 }} h</td>
                                            <td>{{ money($overtime['hours'] * $payroll->hourly_rate, forceDecimals: true) }}</td>
                                            <td class="text-end">{{ __('Gross') }}</td>
                                        </tr>
                                        @endforeach

                                        @foreach($payroll->surcharges as $surcharge)
                                            <tr>
                                                <td>
                                                    {{ __('Surcharge') }} <br>
                                                    {{ $surcharge['description'] }}
                                                </td>
                                                <td>1</td>
                                                <td>{{ money($surcharge['amount'], forceDecimals: true) }}</td>
                                                <td class="text-end">{{ ucfirst($surcharge['tax']) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th>{{ __('Vacation') }}</th>
                                            <td>{{ $payroll->leaves_balance }} Days</td>
                                            <td>{{ money($payroll->leavePayout, forceDecimals: true) }}</td>
                                            <td class="text-end">{{ __('Gross') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Information') }}</th>
                                            <td colspan="3">{{ $payroll->information }}</td>
                                        </tr>
                                        </tbody>

                                        <tbody>
                                        <tr>
                                            <th colspan="3" class="text-end">{{ __('Net Total') }}</th>
                                            <td class="text-end">{{ money($payroll->netTotal, forceDecimals: true) }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">{{ __('Gross Total') }}</th>
                                            <td class="text-end">{{ money($payroll->grossTotal, forceDecimals: true) }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-success btn-sm" wire:click="process">{{ __('Proceed') }}</button>
                        </div>
                    </div>
                </x-tab.content>
            </x-tab>
        </div>
    </div>
</div>
