@extends('layouts.pdf')

@section('content')
    @foreach($payrolls as $payroll)
        @php($user = $payroll->user)
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
                                    <th>{{ $reason->name }}</th>
                                    <td class="text-end">{{ $reason->leaves_this_month }}</td>
                                    <td class="text-end">{{ $reason->leaves_this_year }}</td>
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
                                <td class="text-end">{{ __(':count Days', ['count' => $vacationLastYear]) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('This Year') }}</th>
                                <td class="text-end">{{ __(':count Days', ['count' => $vacationThisYear]) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Taken') }}</th>
                                <td class="text-end">{{ __(':count Days', ['count' => $leaveStats->leaves_taken ?? 0]) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Open') }}</th>
                                <td class="text-end">{{ __(':count Days', ['count' => $leavesBalance]) }}</td>
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
        </div>

        <div class="page-break"></div>
    @endforeach
@endsection

