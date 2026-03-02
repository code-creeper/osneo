<?php

namespace App\Helpers;

use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;

class GeneralHelper
{
    public static function truncateDatabase(array $skip = []): void
    {
        $skip = array_merge($skip, ['migrations', 'settings']);

        $tables = DB::select('SHOW TABLES');
        DB::statement("SET foreign_key_checks=0");
        foreach ($tables as $table) {
            $table = $table->Tables_in_osneo_testing;
            if (in_array($table, $skip)) {
                continue;
            }
            DB::table($table)->truncate();
        }
        DB::statement("SET foreign_key_checks=1");
    }

    public static function objectToArray(object|array $object): array
    {
        if (is_array($object)) {
            return $object;
        }

        return json_decode(json_encode($object), true);
    }

    public static function getDownloadsPath(?string $filename = null): string
    {
        return Storage::disk('public')->path(config('app.downloads_folder'). "/$filename");
    }

    public static function getHolidaysForCalendar(): array
    {
        $dates = app(GeneralSettings::class)->holidays;
        $holidays = [];

        foreach ($dates as $date)
        {
            $holidays[] = [
                'title' => __('Holiday'),
                'start' => Carbon::parse($date)->format('Y-m-d'),
                'resourceIds' => User::pluck('id')->toArray(),
            ];
        }

        return $holidays;
    }

    public static function translateableFieldRules(string $field, array|string $rules): array
    {
        $translationRules = [];

        foreach (getLocales() as $locale => $lang) {
            $translationRules["$field.$locale"] = $rules;
        }

        return $translationRules;
    }
}
