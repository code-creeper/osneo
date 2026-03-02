<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class System
{
    protected static function execute($command): array
    {
        $path = base_path();
        $process = Process::fromShellCommandline('cd '.$path.' && '.$command);
        try {
            $process->mustRun();
            Log::debug($process->getOutput());

            return ['success', $process->getOutput()];
            //return redirect()->back()->with('success', __('Update was successful.'));
        } catch (ProcessFailedException $exception) {
            Log::error($exception->getMessage());

            return ['error', $exception->getMessage()];
            //return redirect()->back()->with('error', __('The update resulted in an error.'));
        }
    }

    //ENV
    public static function retrievingENV(): array
    {
        $data = array();
        $path = base_path('.env');
        $file = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($file as $line) {
            $explode = explode('="', $line);
            if (count($explode) == 1) {
                $explode = explode('=', $line);
            } else {
                $explode[1] = substr_replace($explode[1], "", -1);
            }
            if (in_array($explode[0], config('app.editable_env'))) {
                $data[$explode[0]] = $explode[1];
            }
        }

        return $data;
    }

    public static function updateEnv($key, $value): bool
    {
        if ( ! in_array($key, config('app.editable_env'))) {
            return false;
        }

        $path = base_path('.env');
        $file = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        foreach ($file as $num => $line) {
            $explode = explode('="', $line);
            if (count($explode) == 1) {
                $explode = explode('=', $line);
            } else {
                $explode[1] = substr_replace($explode[1], "", -1);
            }
            if ($explode[0] == $key) {
                $file[$num] = str_replace($explode[1], $value, $file[$num]);
            }
        }
        file_put_contents($path, implode(PHP_EOL, $file), LOCK_EX);

        return true;
    }

    public static function initializeChannel(): void
    {
        $path = base_path('.git/HEAD');
        if ( ! file_exists($path)) {
            return;
        }
        if (config('app.env') == 'local') {
            return;
        }
        $git_version = explode("ref: refs/heads/", file($path)[0])[1];
        if (config('app.channel') != $git_version) {
            self::switchChannel(config('app.channel'));
        }
    }

    public static function diffChannel(): bool
    {
        $path = base_path('.git/HEAD');
        if ( ! file_exists($path)) {
            return false;
        }
        $git_version = explode("ref: refs/heads/", file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)[0])[1];
        if (config('app.channel') != $git_version) {
            return true;
        } else {
            return false;
        }
    }

    //Update
    public static function update(): void
    {
        self::switchChannel(config('app.channel'));
        self::updateGit();
        self::updateComposer();
        self::updateNPM();

        if (config('app.env') == 'production') {
            self::updateDatabase();
        }
        if (config('app.env') != 'production') {
            self::refreshDatabase();
        }

        self::clearCache();
        self::refreshStorageLink();
    }

    public static function switchChannel($channel): void
    {
        self::execute(config('app.git').' checkout '.$channel);
    }

    public static function updateGit(): void
    {
        self::execute(config('app.git').' pull origin '.config('app.channel'));
    }

    public static function updateComposer(): void
    {
        self::execute(config('app.composer').' install');
    }

    public static function updateNPM(): void
    {
        self::execute(config('app.npm').' install');
        self::execute(config('app.npm').' run dev');
    }

    //Clear Cache
    public static function clearCache(): void
    {
        self::clearCacheLaravel();
        self::clearCacheComposer();
        self::clearCacheNPM();
    }

    public static function clearCacheLaravel(): void
    {
        Artisan::call('optimize:clear');
        if (config('app.env') == 'production') {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            Artisan::call('event:cache');
        }
    }

    public static function clearCacheComposer(): void
    {
        self::execute(config('app.composer').' clear-cache');
        self::execute(config('app.composer').' dump-autoload');
    }

    public static function clearCacheNPM(): void
    {
        self::execute(config('app.npm').' cache clean --force');
    }

    public static function clearLanguageLog(): void
    {
        Artisan::call('activitylog:clean language --days=0 --force');
    }

    //Refresh
    public static function refreshDatabase()
    {
        if (config('app.env') == 'production') {
            return redirect()->back()->with('error', __('An error has occurred!'));
        }
        Artisan::call('migrate:fresh --seed');
    }

    public static function updateDatabase(): void
    {
        Artisan::call('migrate');
    }

    public static function refreshStorageLink(): void
    {
        $path = base_path();
        self::execute('rm '.$path.'/public/storage');
        Artisan::call('storage:link');
    }

    //Maintenance mode
    public static function checkMaintenance(): bool
    {
        $path = base_path('storage/framework/down');
        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    public static function up(): void
    {
        Artisan::call('up');
    }

    public static function down(): void
    {
        Artisan::call('down');
    }

    public static function downWithSecret(): string
    {
        $hash = Str::random(36);
        Artisan::call('down --secret="'.$hash.'"');

        return $hash;
    }
}
