<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-errors/>
        <x-form-input wrapper-class="col-6" name="user.first_name" label="First Name"/>
        <x-form-input wrapper-class="col-6" name="user.last_name" label="Last Name"/>
        <x-form-input wrapper-class="col-12" name="user.email" label="Email"/>
        <x-form-select2
                wrapper-class="col-6" label="Primary Role" name="user.role_id"
                :options="$primaryRoles" placeholder="Select Role"
        />
        <x-form-select2
                wrapper-class="col-6" label="Secondary Roles"
                name="secondaryRoleIds" :options="$secondaryRoles" :multiple="true"
        />
        <x-form-input wrapper-class="col-6" name="user.ssn" label="Social Security Number"/>
        <x-form-input wrapper-class="col-6" name="user.birth_name" label="Birth Name"/>
        <x-form-input wrapper-class="col-6" name="user.birthplace" label="Birth Place"/>
        <x-form-flatpickr wrapper-class="col-6" name="dob" label="Date of Birth"/>
        <x-form-select wrapper-class="col-6" name="user.gender" label="Gender" :options="$genders" />
        <x-form-select wrapper-class="col-6" name="user.active" label="Status" :options="$statuses" />
        <x-form-input wrapper-class="col-12" name="user.address" label="Address"/>
        <x-form-flatpickr wrapper-class="col-6" name="activateOn" label="Activate on"/>
        <x-form-flatpickr wrapper-class="col-6" name="deActivateOn" label="Deactivate on"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
