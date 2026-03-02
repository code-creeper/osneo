<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <div class="col-12 text-center">
            <h4><b>{{ __('Status') }}</b>: {{ $claim->status }}</h4>
        </div>

        @if($claim->isOpen())
            <div class="col-12">
                <table class="table table-sm table-bordered table-striped">
                    <tr>
                        <th>{{ __('Case Number') }}</th>
                        <td>{{ $claim->claim_number }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Health insurance number') }}</th>
                        <td>{{ $leave->user->health_insurance_number }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Social security number') }}</th>
                        <td>{{ $leave->user->ssn }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Gender') }}</th>
                        <td>{{ ucfirst($leave->user->gender) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('First Name') }}</th>
                        <td>{{ $leave->user->first_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Last Name') }}</th>
                        <td>{{ $leave->user->last_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Birth Name') }}</th>
                        <td>{{ $leave->user->birth_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Date of birth') }}</th>
                        <td>{{ $leave->user->dob?->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Place of birth') }}</th>
                        <td>{{ $leave->user->birthplace }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Sick since') }}</th>
                        <td>{{ $leave->starts_on?->format('d.m.Y') }}</td>
                    </tr>
                </table>
            </div>
        @endif

        @if($claim->isWaiting())
            <div class="col-12">
                <x-form-group class="col-12" label="Insurance Response" inline>
                    <x-form-radio name="response" value="accepted" label="Accepted"/>
                    <x-form-radio name="response" value="rejected" label="Rejected"/>
                    <x-form-radio name="response" value="partially_accepted" label="Partially Accepted"/>
                </x-form-group>
            </div>

            <div class="col-12" x-show="$wire.response === 'partially_accepted'">
                <div class="row">
                    <x-form-flatpickr name="starts_on" label="Start Date" :config="$datePickerConfig"/>
                    <x-form-flatpickr name="ends_on" label="End Date" :config="$datePickerConfig"/>
                </div>
            </div>
        @endif

        @if($claim->isConfirmed())
            <div class="col-12 text-center mt-0">
                <p class="text-muted fs-4">{{ __('Click on "Process" button below to confirm the payment and close the claim') }}</p>
            </div>
        @endif

        @if($claim->isUnconfirmed())
            <div class="col-12 text-center mt-0">
                <p class="text-muted fs-4">{{ __('The claim was previously rejected. You can request again after ').
                $claim->last_requested_on->addWeeks(2)->format('d-m-Y') }}</p>
            </div>
        @endif

        @if($claim->acceptsDocument())
            <div class="col-12">
                <h5>{{ __('Upload Document') }}</h5>
                <x-media-library-attachment name="documents"/>
            </div>
        @endif
    </div>

    <x-slot name="buttons">
        <button class="btn btn-sm btn-success" type="submit" @disabled($claim->isUnconfirmed())>
            {{ __('Process') }}
        </button>
        <button class="btn btn-sm btn-primary" type="button" wire:modal="close">
            {{ __('Cancel') }}
        </button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>

