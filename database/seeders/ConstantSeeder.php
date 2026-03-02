<?php

namespace Database\Seeders;

use App\Models\Constant;
use Illuminate\Database\Seeder;

class ConstantSeeder extends Seeder
{
    public function run()
    {
        $constantGroups = [
            'tank_levels'     => [
                'empty'         => 'Empty',
                'quarter'       => '¼',
                'half'          => '½',
                'three_quarter' => '¾',
                'full'          => 'Full',
            ],
            'damage_statuses' => [
                'pending' => 'Pending',
            ],
        ];


        foreach ($constantGroups as $group => $constants) {
            foreach ($constants as $key => $constant){
                Constant::updateOrCreate(
                    [
                        'key' => $key,
                        'group' => $group
                    ],
                    ['value' => $constant,]
                );
            }
        }
    }
}
