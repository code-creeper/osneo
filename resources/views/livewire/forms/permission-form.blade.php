<x-wire-elements-pro::bootstrap.slide-over on-submit="submit">

    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row">
        <x-form-select
            name="roleId" wire:model.live="selectedRoleId" label="Role" container-class="col-12"
            wire:change="handleSelectedRole" :options="$roles"
        />

        <div class="col-12"><hr></div>

        <div class="col-12" wire:loading.remove wire:target="handleSelectedRole">
            @foreach($permissionGroups as $group)
                <div class="row">
                    <h5 class="mb-3">{{ str($group)->replace('_', ' ')->title() }}</h5>
                    @foreach(\Spatie\Permission\Models\Permission::where('group', $group)->get() as $permission)
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" id="permission_{{ $permission->id }}"  wire:model="permissions"
                                       type="checkbox" value="{{ (int)$permission->id }}"/>
                                <label class="form-check-label"
                                       for="permission_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr>
            @endforeach
        </div>
    </div>

    <x-slot name="buttons">
        <div class="text-end">
            <button class="btn btn-sm btn-success" type="submit">
                {{ __('Save Changes') }}
            </button>
            <button class="btn btn-sm btn-primary" type="button" wire:slide-over="close">
                {{ __('Cancel') }}
            </button>
        </div>
    </x-slot>
</x-wire-elements-pro::bootstrap.slide-over>
