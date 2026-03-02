<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Role::updateOrCreate([
            'name'         => 'employee',
            'display_name' => 'Employee',
        ]);

        $admin = User::create([
            'first_name' => 'Robert',
            'last_name' => 'Conn',
            'email' => 'admin@system.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'user@system.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('employee');
    }
}
