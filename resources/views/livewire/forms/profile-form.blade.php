<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-errors/>
        <x-form-input wrapper-class="col-6" name="user.first_name" label="First Name"/>
        <x-form-input wrapper-class="col-6" name="user.last_name" label="Last Name"/>

        @wire(false)
            <x-form-input wrapper-class="col-12" name="email" label="Email" :default="$user->email" disabled />
        @endwire

        <x-form-input wrapper-class="col-6" name="user.ssn" label="Social Security Number"/>
        <x-form-input wrapper-class="col-6" name="user.birth_name" label="Birth Name"/>
        <x-form-select wrapper-class="col-6" name="user.gender" label="Gender" :options="$genders" />
        <x-form-flatpickr wrapper-class="col-6" name="dob" label="Date of Birth"/>
        <x-form-input wrapper-class="col-6" name="user.birthplace" label="Birth Place"/>
        <x-form-input wrapper-class="col-6" name="user.address" label="Address"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
