<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        \App\Models\Constant::create([
            'group' => "contract_sections",
            'key' => "introduction",
            "value" => config('lexoffice.defaults.introduction')
        ]);

        \App\Models\Constant::create([
            'group' => "contract_sections",
            'key' => "remarks",
            "value" => config('lexoffice.defaults.remarks')
        ]);
    }

    public function down()
    {
    }
};
