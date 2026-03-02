<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class EmploymentSeeder extends Seeder
{
    public function run()
    {
        $users = User::whereDoesntHave('employment')->get();

        foreach ($users as $user){
            $user->employments()->create([
                'started_on' => $user->created_at,
                'target_hours_type' => 'weekly',
                'target_hours' => 40,
                'off_days' => [],
            ]);
        }
    }
}
