<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'admin' => 'Administrator',
        ];

        foreach ($roles as $role => $display_name) {
            Role::updateOrCreate(
                ['name' => $role],
                ['display_name' => $display_name]
            );
        }

        foreach (config('permissions') as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate([
                    'group' => $group,
                    'name' => $permission,
                ], []);
            }
        }

        //$this->assignPermissions($permissionsArray);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function assignPermissions($permissionsArray)
    {
        $admin = Role::updateOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->givePermissionTo(Permission::all());
    }
}
