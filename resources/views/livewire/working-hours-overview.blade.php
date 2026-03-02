<div class="card">
    <x-loader/>
    <div class="card-header d-flex flex-column">
        <div class="d-flex mb-2">
            <h5 class="me-auto">{{ $heading }}</h5>
            <div class="float-end">
            </div>
        </div>

    <!-- Filters Start -->
        <div class="d-flex g-2 row">
            <div class="col-3 position-relative" id="datepicker">
                <label for="date">{{ __('Date') }}</label>
                <input id="date" type="month" class="form-control form-control-sm"
                       wire:model.live="date" wire:change="onChangeDate">
            </div>

            <div class="align-self-end ms-2 col">
                <button class="btn btn-primary btn-sm" wire:click="resetFilters">{{ __('Reset') }}</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0 px-2">
        <div class="mb-3 table-nowrap scrollbar table-responsive">
            <table class="table table-sm  table-hover fs--1">
                <thead class="bg-200 text-900">
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Paid Leaves') }}</th>
                    <th>{{ __('Sick Leaves') }}</th>
					<th>{{ __('Child Sick Leaves') }}</th>
                    <th>
                        <!-- Total hours a user have worked (hours logged from attendance) !-->
                        {{ __('Hours Worked') }}
                    </th>
					<th>
                        <!-- Total hours a user should be credited i.e working hours + paid leaves etc !-->
                        {{ __('Total Payable Hours') }}
                    </th>
                    <th>{{ __('Overtime for Current Month') }}</th>
                    <th>{{ __('Total Overtime') }}</th>
					<th>{{ __('Payout') }}</th>
                    <th>{{ __('Comments') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="list">
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->paid_leaves_count ?? 0 }}</td>
                        <td>{{ $user->sick_leaves_count ?? 0 }}</td>
						<td>{{ $user->child_sick_leaves_count ?? 0 }}</td>
                        <td>{{ formatHrs($user->working_hours, true) }}</td>
						<td>{{ formatHrs($user->creditable_hours, true) }}</td>
                        <td>{{ formatHrs($user->overtime, true) }}</td>
                        <td>{{ formatHrs($user->total_overtime, true) }}</td>
						<td>{{ formatHrs($user->payout, true) }}</td>
                        <td>
                            @if($user->auto_checkout_count)
                                <span class="text-warning cursor-pointer"
                                      wire:modal="modals.auto-checkout-entries, @js(['user' => $user->id, 'monthWithYear' => $date])">
                                    <i class="fad fa-exclamation-circle"></i>
                                </span>
                            @else
                                <span class="text-success"><i class="fad fa-check-circle"></i></span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <button class="btn btn-primary btn-sm"
                                        wire:modal="modals.add-payout, @js([
                                            'user' => $user->id,
                                            'overtime' => $user->id,
                                            'monthWithYear' => $date
                                        ])">
                                    {{ __('Payout') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-0 border-top-0">
        <div class="mt-1 d-flex justify-content-between" style="line-height: 1;">
            <p class="fs--1 align-self-center">
                <select wire:model.live="perPage" class="form-control form-control-sm d-inline" style="width: auto"
                        id="per_page">
                    @foreach($perPageOptions as $option)
                        <option value="{{$option}}">{{$option}}</option>
                    @endforeach
                </select>
                <span>Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ number_format($users->total()) }}
                    entries</span>
            </p>
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
