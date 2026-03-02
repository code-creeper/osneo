<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdatePermissionsCommand extends Command
{
    protected $signature = 'permission:update';

    protected $description = 'Update Permissions';

    public function handle(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'PermissionSeeder',
            '--force' => true
        ]);

        $this->info("Permissions updated");
    }
}
