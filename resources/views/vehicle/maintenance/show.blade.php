@php($current_condition = $current_condition ?? false)

<div class="row {{ $current_condition ? '' : 'm-3 border border-primary' }} g-0">

    @if($current_condition)
        <div class="col-lg-3">
            <div class="card shadow-none">
                <div class="card-body">
                    <h4 class="header-title mb-3">{{ __('General Info') }}</h4>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <p class="mb-2"><span class="fw-bold me-2">{{ __('Name') }}:</span> {{ $vehicle->name }}</p>
                            <p class="mb-2"><span class="fw-bold me-2">{{ __('License Plate') }}
                                    :</span> {{ $vehicle->license_plate }}</p>
                            <p class="mb-2"><span class="fw-bold me-2">{{ __('Ticket Number') }}
                                    :</span> {{ $vehicle->ticket_number }}</p>
                            <p class="mb-2"><span class="fw-bold me-2">{{ __('Last Updated On') }}
                                    :</span> {{ $vehicle->last_updated_on }}</p>
                            <p class="mb-0"><span class="fw-bold me-2">{{ __('Driver') }}
                                    :</span> {{ $vehicle->driver?->name }}</p>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    @endif
    <div class="col-lg-3">
        <div class="card shadow-none">
            <div class="card-body">
                <h4 class="header-title mb-3">{{ __('Condition Info') }}</h4>
                <ul class="list-unstyled mb-0">
                    <li>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Mileage') }}:</span>
                            {{ $condition->mileage }} KM
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Outside Condition') }}:</span>
                            {{ $condition->getConstantText('outside_condition') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Inside Condition') }}:</span>
                            {{ $condition->getConstantText('inside_condition') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Tank Level') }}:</span>
                            {{ $condition->getConstantText('tank_level') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Warning Triangle') }}:</span>
                            {{ $condition->getConstantText('warning_triangle') }}
                        </p>
                    </li>
                </ul>

            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="card shadow-none">
            <div class="card-body">
                <h4 class="header-title mb-3">{{ __('Documents Info') }}</h4>
                <ul class="list-unstyled mb-0">
                    <li>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Craftsman\'s License') }}:</span>
                            {{ $condition->getConstantText('craftsman_license') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Registration') }}:</span>
                            {{ $condition->getConstantText('registration') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Service Booklet') }}:</span>
                            {{ $condition->getConstantText('service_booklet') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('MOT Date') }}:</span>
                            {{ $condition->mot_date }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Next Maintenance') }}:</span>
                            {{ $condition->next_maintenance_date }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Emission Sticker') }}:</span>
                            {{ $condition->emission_sticker }}
                        </p>
                    </li>
                </ul>

            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="card shadow-none">
            <div class="card-body">
                <h4 class="header-title mb-3">{{ __('Equipment Info') }}</h4>
                <ul class="list-unstyled mb-0">
                    <li>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Gas Card') }}:</span>
                            {{ $condition->getConstantText('gas_card') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Safety Vest') }}:</span>
                            {{ $condition->getConstantText('safety_vest') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('First Aid Kit') }}:</span>
                            {{ $condition->getConstantText('first_aid_kit') }}
                        </p>
                    </li>
                </ul>

            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="card shadow-none">
            <div class="card-body">
                <h4 class="header-title mb-3">{{ __('Tire Depth Profile') }}</h4>
                <ul class="list-unstyled mb-0">
                    <li>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Front Left') }}:</span>
                            {{ $condition->getConstantText('front_left_tyre_profile') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Front Right') }}:</span>
                            {{ $condition->getConstantText('front_right_tyre_profile') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Back Left') }}:</span>
                            {{ $condition->getConstantText('back_left_tyre_profile') }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold me-2">{{ __('Back Right') }}:</span>
                            {{ $condition->getConstantText('back_right_tyre_profile') }}
                        </p>
                    </li>
                </ul>

            </div>
        </div>
    </div>
        @if(! $current_condition)
            <div class="col-12 text-end px-2">
                <h5 class="text-muted">{{ __('Updated By') }}: {{ ($condition->user ?? \App\Models\User::forceFind($condition->user_id))->name }}</h5>
                <h6 class="text-muted">{{ __('Date') }}: {{ $condition->created_at->format(config('dates.default')) }}</h6>
            </div>
        @endif
</div>
