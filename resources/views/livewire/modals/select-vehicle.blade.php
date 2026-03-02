<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2" x-data="{
        step(){
            if ( ! $wire.vehicle_id || ( ! $wire.showMaintenanceForm && ! $wire.confirmationRequired)) {
                return 'vehicle-selection';
            }

            if ($wire.confirmationRequired) {
                return 'vehicle-confirmation';
            }

            if ($wire.showMaintenanceForm) {
                return 'maintenance-form';
            }
        },

        cancelSelection(){
            $wire.confirmationRequired = false;
            $wire.showMaintenanceForm = false;
        }
    }">
        <template x-if="step() === 'vehicle-selection'">
            <div class="row g-2">
                <div class="col-12">
                    <div class="list-group">
                        @foreach($vehicles as $vehicle)
                            <button type="button"
                                    :class="{ 'active': $wire.vehicle_id === @js($vehicle->id) }"
                                    class="list-group-item list-group-item-action"
                                    @click="$wire.vehicle_id = @js($vehicle->id)">
                                {{ $vehicle->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <div>
                        <button type="button" class="btn btn-sm btn-danger me-2"
                                wire:click="noVehicleSelected">{{ __('Not Using Vehicle') }}</button>

                        <button type="button" class="btn btn-sm btn-success" :class="{ 'disabled': !$wire.vehicle_id }"
                                wire:click="onVehicleSelected">{{ __('Select') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="step() === 'vehicle-confirmation'">
            <div class="col-12">
                <div class="alert alert-warning fs-4" role="alert">
                    <i class="fal fa-exclamation-circle"></i>
                    {{ __('The vehicle you selected is already in use. Are you sure you want to select this vehicle?') }}
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <div>
                        <button type="button" class="btn btn-sm btn-primary me-2" @click="cancelSelection()">
                            <i class="fal fa-long-arrow-left"></i>
                            {{ __('Back') }}
                        </button>

                        <button type="button" class="btn btn-sm btn-success" @click="$wire.confirmationRequired = false">
                            {{ __('Continue') }}
                            <i class="fal fa-long-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="step() === 'maintenance-form'">
            <div class="row g-2">
                <x-errors/>
                <x-form-input name="history.mileage" label="Mileage" />

                <div class="row mb-3 g-2">
                    <h5 class="col-12 mb-0">{{ __('Condition Info') }}</h5>

                    <x-form-group class="col-6" label="Outside Vehicle Condition" inline>
                        @foreach(config('constants.vehicle_conditions') as $key => $condition)
                            <x-form-radio name="history.outside_condition" :value="$key" :label="$condition"/>
                        @endforeach
                    </x-form-group>

                    <x-form-group class="col-6" label="Inside Vehicle Condition" inline>
                        @foreach(config('constants.vehicle_conditions') as $key => $condition)
                            <x-form-radio name="history.inside_condition" :value="$key" :label="$condition"/>
                        @endforeach
                    </x-form-group>

                    <x-form-group class="col-6" label="Tank Level" inline>
                        @foreach(config('constants.tank_levels') as $key => $level)
                            <x-form-radio name="history.tank_level" :value="$key" :label="$level"/>
                        @endforeach
                    </x-form-group>
                </div>
                <hr>

                <div class="row mb-3">
                    <h5 class="col-12">{{ __('Equipment Info') }}</h5>

                    <x-partials.vehicle-condition-yes-no field="history.gas_card" label="Gas card"/>
                    <x-partials.vehicle-condition-yes-no field="history.safety_vest" label="Safety vest"/>
                    <x-partials.vehicle-condition-yes-no field="history.first_aid_kit" label="First aid kit"/>

                    <div class="col" x-show="$wire.history.first_aid_kit === 'yes'">
                        <x-form-flatpickr name="firstAidKitExpiryDate" label="First aid kit Expiry" class="form-control-sm"/>
                    </div>

                    <x-partials.vehicle-condition-yes-no field="history.warning_triangle" label="Warning Triangle"/>
                </div>
                <hr>

                <div class="row mb-2">
                    <h5 class="col-12">{{ __('Documents Info') }}</h5>

                    <x-partials.vehicle-condition-yes-no field="history.registration" label="Registration"/>
                    <x-partials.vehicle-condition-yes-no field="history.service_booklet" label="Service Booklet"/>
                    <x-partials.vehicle-condition-yes-no field="history.craftsman_license" label="Craftsman's License"/>

                    <div class="col" x-show="$wire.history.craftsman_license === 'yes'">
                        <x-form-flatpickr
                            name="craftsmanLicenseExpiryDate" label="Craftsman's License Expiry" class="form-control-sm"
                        />
                    </div>
                </div>
                <div class="row mb-3">
                    <x-form-group class="col-12 mb-2" label="Emission Sticker" inline>
                        @foreach(config('constants.emission_stickers') as $key => $level)
                            <x-form-radio name="history.emission_sticker" :value="$key" :label="$level"/>
                        @endforeach
                    </x-form-group>

                    <x-form-flatpickr
                        wrapper-class="col" name="nextMaintenanceDate"
                        label="Next Maintenance Date" class="form-control-sm"
                    />
                    <x-form-flatpickr
                        wrapper-class="col" name="motDate"
                        label="Next Mot Date" class="form-control-sm"
                    />
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-12">
                        <h5>{{ __('Tyre Profile Depth') }}
                            <i class="fal fa-question-circle ms-1 cursor-pointer" id="tyre_depth_exp"></i>
                            <img id="tyre_depth_img" class="img-thumbnail w-25 mb-2" style="display: none"
                                 src="{{ asset('images/tyre-depth.jpg') }}">
                        </h5>
                    </div>

                    <x-partials.vehicle-condition-yes-no field="history.front_left_tyre_profile" label="Front Left"/>
                    <x-partials.vehicle-condition-yes-no field="history.front_right_tyre_profile" label="Front Right"/>
                    <x-partials.vehicle-condition-yes-no field="history.back_left_tyre_profile" label="Back Left"/>
                    <x-partials.vehicle-condition-yes-no field="history.back_right_tyre_profile" label="Back Right"/>
                </div>
                <hr>
                <div class="col-12">
                    <x-form-checkbox wrapper-class="mb-2" name="damagesReviewed">
                        <x-slot:label>
                            @if($vehicle->damages->count())
                                {{ __('I have checked the damages listed')}}
                                <a href="{{ route('vehicles.show', [$vehicle_id, 'tab' => 'damages']) }}"
                                   target="_blank">{{ __('here') }}</a>
                            @else
                                {{ __('I confirm there is no damage')}}
                            @endif
                        </x-slot:label>
                    </x-form-checkbox>

                    <a href="#" class="mt-2" wire:modal="forms.damage-form, @js(['vehicle' => $vehicle_id])">
                        Report new damage
                    </a>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <div>
                        @if($vehicleChangeable)
                            <button type="button" class="btn btn-sm btn-primary" @click="$wire.showMaintenanceForm = false">
                                <i class="fal fa-long-arrow-left"></i>
                                {{ __('Back') }}
                            </button>
                        @endif
                        <button type="submit" class="btn btn-sm btn-success">{{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-wire-elements-pro::bootstrap.modal>
