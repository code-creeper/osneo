<?php


namespace App\Settings;


use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public array $holidays;

    public static function group(): string
    {
        return 'general';
    }
}
