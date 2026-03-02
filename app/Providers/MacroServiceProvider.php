<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use Str;

class MacroServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(GeneralSettings $settings): void
    {
        //TODO::improvement: should check for both date formats

        Carbon::macro('isHoliday', fn() => (bool) count(
            array_intersect([
                $this->format('d-m-Y'),
                $this->format('j-n-Y'),
            ], $settings->holidays)
        ));

        Carbon::macro('isOffDay', function ($offDays = array()) {
            return in_array(strtolower($this->format('l')), $offDays);
        });

        Carbon::macro('isWorkingDay', function ($offDays = array()) {
            return ! $this->isHoliday() && ! $this->isOffDay($offDays);
        });

        Carbon::macro('lastFridayOfMonth', function () {
            return $this->lastOfMonth(5);
        });

        Builder::macro('past', function ($column, $when = 'now', $strict = false) {
            $when = Carbon::parse($when);
            $operator = $strict ? '<' : '<=';

            return $this->where($column, $operator, $when);
        });

        Builder::macro('future', function ($column, $when = 'now', $strict = false) {
            $when = Carbon::parse($when);
            $operator = $strict ? '>' : '>=';

            return $this->where($column, $operator, $when);
        });

        Builder::macro('whereMonthOfYear', function ($column, $month, $year) {
            return $this->where(fn($query) => $query
                ->whereMonth($column, $month)
                ->whereYear($column, $year)
            );
        });

        Builder::macro('whereLike',
            fn($column, $search) => $this->where($column, 'LIKE', "%{$search}%")
        );

        Builder::macro('orWhereLike',
            fn($column, $search) => $this->orWhere($column, 'LIKE', "%{$search}%")
        );

        EloquentBuilder::macro('whereRelationLike',
            fn($relation, $column, $value = null) => $this->whereRelation($relation, $column, 'LIKE', "%{$value}%")
        );

        EloquentBuilder::macro('orWhereRelationLike',
            fn($relation, $column, $value = null) => $this->orWhereRelation($relation, $column, 'LIKE', "%{$value}%")
        );

        Builder::macro('orWhereMonthOfYear', fn($column, $month, $year) => $this->orWhere(
            fn($query) => $query->whereMonthOfYear($column, $month, $year)
        ));

        Builder::macro('whereDateFormat', function ($column, $value, $format) {
            $format = preg_replace('/[a-zA-Z]/', '%$0', $format);
            return $this->whereRaw("DATE_FORMAT(`$column`, '$format') = '$value'");
        });

        Builder::macro('except', function ($ids) {
            if ( ! is_array($ids)) {
                $ids = array($ids);
            }

            return $this->whereNotIn('id', $ids);
        });

        Carbon::macro('addDaysWhere', function ($days, $callback) {
            $date = $this->copy();
            $addedDays = 0;

            while ($addedDays < $days) {
                $date->addDay();

                if ( ! $callback($date)) {
                    continue;
                }

                $addedDays++;
            }

            return $date;
        });

        Carbon::macro('setDefaultTz', function () {
            return $this->toAppTz();
        });

        Carbon::macro('toAppTz', function () {
            return $this->timezone(config('app.timezone'));
        });

        Carbon::macro('date', function () {
            return $this->format(config('dates.default'));
        });

        Stringable::macro('doesntMatch', fn($pattern) => ! $this->isMatch($pattern));
        Str::macro('doesntMatch', fn($pattern, $value) => ! Str::isMatch($pattern, $value));


        Collection::macro('toKeyValuePair', function ($key = 'id', $value = 'name'){
            return $this->keyBy($key)->pluck($value, $key)->toArray();
        });

        DB::macro('enableForeignKeyChecks', fn() => $this->statement('SET foreign_key_checks = 1'));
        DB::macro('disableForeignKeyChecks', fn() => $this->statement('SET foreign_key_checks = 0'));

        DB::macro('truncate', function ($table) {
            DB::transaction(fn() => DB::statement("TRUNCATE  TABLE $table"));
        });

        DB::macro('forceTruncate', function ($table) {
            DB::transaction(function () use ($table){
                DB::disableForeignKeyChecks();
                DB::truncate($table);
                DB::enableForeignKeyChecks();
            });
        });

        Builder::macro('forceTruncate', function () {
            DB::transaction(function (){
                DB::disableForeignKeyChecks();
                $this->truncate();
                DB::enableForeignKeyChecks();
            });

        });

        EloquentBuilder::macro('resetAutoIncrement', function () {
            $table = $this->getModel()->getTable();
            DB::statement("ALTER TABLE $table AUTO_INCREMENT = 1");
        });

        DB::macro('enableForeignKeyChecks', fn() => $this->statement('SET foreign_key_checks = 1'));
        DB::macro('disableForeignKeyChecks', fn() => $this->statement('SET foreign_key_checks = 0'));

        Builder::macro('forceTruncate', function () {
            DB::transaction(function (){
                DB::disableForeignKeyChecks();
                $this->truncate();
                DB::enableForeignKeyChecks();
            });

        });

        EloquentBuilder::macro('resetAutoIncrement', function () {
            $table = $this->getModel()->getTable();
            DB::statement("ALTER TABLE $table AUTO_INCREMENT = 1");
        });
    }
}
