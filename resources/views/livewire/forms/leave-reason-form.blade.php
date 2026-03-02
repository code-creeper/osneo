<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <x-errors/>
    <div class="row g-2">
        <x-multi-lingual-form-input wrapper-class="col-6" name="name" label="Name"/>
        <x-form-input wrapper-class="col-6" type="color" name="leaveReason.color" label="Color"/>
        <div class="col-12">
            <x-form-checkbox wrapper-class="mb-2" name="leaveReason.paid" label="Paid" :custom-control="false"/>
            <x-form-checkbox name="leaveReason.deductible" label="Deductible" :custom-control="false"/>
        </div>
    </div>
    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
