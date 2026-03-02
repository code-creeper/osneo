<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-3" x-data="{showCode: true}">
        <x-errors/>
        <x-form-input name="announcement.subject" label="Subject"/>

        <label>
            <span>{{ __('Body') }}</span>
            <i  @click="showCode = !showCode" class="fal fa-code btn btn-sm ps-1 text-info"></i>
        </label>

        <template x-if="showCode">
            <x-form-textarea wrapper-class="mt-0" name="announcement.body" :autresize="true" x-intersect="resize" />
        </template>

        <template x-if="!showCode">
            <div class="col-12 mt-0">
                <x-alpine.quill wire:model="announcement.body"/>
            </div>
        </template>

        <x-form-group class="col-12" label="Select Audience" inline>
            <x-form-radio name="announcement.audience" value="all" label="All Users"/>
            <x-form-radio name="announcement.audience" value="role" label="Role"/>
            <x-form-radio name="announcement.audience" value="user" label="User"/>
        </x-form-group>

        <template x-if="$wire.announcement.audience === 'user'">
            <x-form-select2 label="Select Users" name="userIds" :options="$users" :multiple="true"/>
        </template>

        <template x-if="$wire.announcement.audience === 'role'">
            <x-form-select2 label="Select Roles" name="roleIds" :options="$roles" :multiple="true"/>
        </template>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>

@assets
<script src="{{ asset("assets/js/vendor/quill.min.js") }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/vendor/quill.snow.css") }}">
<script></script>
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/vendor/quill.core.css") }}">
@endassets
