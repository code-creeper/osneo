<x-layout>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('General Settings') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.general.update') }}" method="post">
                @csrf
                <div class="mb-3 position-relative"
                     x-data="{
                        holidays: @js(implode(',', $settings->holidays)),
                        init(){
                            let $picker = $('#holidays_picker');

                            $picker.on('changeDate', () => {
                                this.holidays = $picker.datepicker('getFormattedDate');
                            });
                        }
                     }"
                >
                    <label class="form-label">{{ __('Holidays') }}</label>
                    <div x-ref="picker" id="holidays_picker" data-provide="datepicker-inline" data-date-multidate="true"
                         data-date-format="d-m-yyyy" data-date="{{ implode(',', $settings->holidays) }}"></div>

                    <input x-model="holidays" type="hidden" name="holidays">

                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">
                        {{ __('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>

