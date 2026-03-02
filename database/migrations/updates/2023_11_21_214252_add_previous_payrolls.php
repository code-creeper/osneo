<?php

use App\Models\Payroll;
use Carbon\CarbonPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $months = CarbonPeriod::create('2021-01-01', '1 month', now()->subMonth());

        foreach ($months as $month){
            Artisan::call('payroll:create', [
                'date' => $month
            ]);
        }
    }

    public function down(): void
    {
        Payroll::truncate();
    }
};
