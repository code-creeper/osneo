<x-wire-elements-pro::bootstrap.modal :close-icon="false" :$headless :$title>
    <x-errors/>

    <div class="row">
        @if($employments->count())
            <div class="col-3">
                <div class="list-group">
                    @foreach($employments as $employment)
                        <button
                            class="list-group-item list-group-item-action {{ $employment->id == $selectedEmployment->id ? 'active' : '' }}"
                            type="button" wire:click="selectEmployment({{ $employment->id }})">{{ $employment->period }}
                        </button>
                    @endforeach
                    <button class="list-group-item list-group-item-action {{ $creatingNewEmployment ? 'active' : '' }}"
                            type="button" wire:click="openNewForm">
                        <span class="me-1">{{ __('Add New Employment') }}</span>
                        <i class="fal fa-plus-square text-{{$creatingNewEmployment ? 'white' : 'primary' }}"></i>
                    </button>
                </div>
            </div>
        @endif
        <div class="col">
            <div class="row g-3 ">
                <x-form-flatpickr wrapper-class="col-6" name="started_on" label="Starts on"/>
                <x-form-flatpickr wrapper-class="col-6" name="ended_on" label="Ends on"/>

                <x-form-select
                    wrapper-class="col-3" name="selectedEmployment.employment_type"
                    label="Employment Type" :options="$employment_types"
                />

                <x-form-input
                    wrapper-class="col" label="Weekly Target Time (Minutes)"
                    name="selectedEmployment.weekly_target_time"
                />

                <template x-if="$wire.selectedEmployment.employment_type === 'hourly'">
                    <x-form-input
                        wrapper-class="col" label="Monthly Target Time (Minutes)"
                        name="selectedEmployment.monthly_target_time"
                    />
                </template>

                <x-form-input
                    wrapper-class="col" label="Hourly Rate"
                    name="selectedEmployment.hourly_rate"
                />

                @php($days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])

                <div class="col-12">
                    <label class="form-label">{{ __('Days off') }}</label>
                    <div class="mt-1">

                        @foreach($days as $day)
                            <div class="form-check form-check-inline ps-0">
                                <input type="checkbox" id="days_off_{{$day}}" wire:model="off_days" value="{{$day}}"
                                       data-switch="danger"
                                />
                                <label for="days_off_{{$day}}" style="top: 6px;"></label>
                                <label class="form-check-label" for="days_off_{{$day}}">{{ __(ucfirst($day))}}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-end">
                    @if(!$creatingNewEmployment)
                        <button class="btn btn-danger me-2" type="button" wire:click="delete">
                            {{ __('Delete') }}
                        </button>
                    @endif
                    @if(!$creatingNewEmployment)
                        <a class="btn btn-primary me-2" type="button"
                           href="{{ route('user.show-employment', $selectedEmployment->id) }}">
                            {{ __('View') }}
                        </a>
                    @endif
                    <button class="btn btn-primary" wire:click="submit" type="button">
                        {{ __($creatingNewEmployment ? 'Create' : 'Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-wire-elements-pro::bootstrap.modal>
