<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class UpdateUserStatusCommand extends Command
{
    protected $signature = 'user:update-status';

    protected $description = 'Activate or Deactivate users';

    public function handle(): void
    {
        $users = User::withoutGlobalScopes()
            ->where(fn(Builder $builder) => $builder
                ->orWhereDate('activate_on', '<=', now()->toDateString())
                ->orWhereDate('deactivate_on', '<=', now()->toDateString())
            )
            ->get();

        foreach ($users as $user) {
            if ( ! $user->active && $user->activate_on?->isPast()) {
                $user->update([
                    'active' => 1,
                    'activate_on' => null
                ]);
                \Log::info("User $user->name is activated");
            }

            if ($user->active && $user->deactivate_on?->isPast()) {
                $user->update([
                    'active' => 0,
                    'deactivate_on' => null
                ]);
                \Log::info("User $user->name is deactivated");
            }
        }

        $this->info('Done!');
    }
}
