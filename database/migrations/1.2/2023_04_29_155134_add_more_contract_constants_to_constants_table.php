<?php

use App\Models\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('constants', function (Blueprint $table) {
            Constant::create([
                'group' => "contract_sections",
                'key' => "title",
                "value" => config('lexoffice.defaults.title')
            ]);

            Constant::create([
                'group' => "contract_sections",
                'key' => "payment_terms_label",
                "value" => config('lexoffice.defaults.payment_terms.label')
            ]);

            Constant::create([
                'group' => "contract_sections",
                'key' => "payment_terms_duration",
                "value" => config('lexoffice.defaults.payment_terms.duration')
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('constants', function (Blueprint $table) {
            //
        });
    }
};
