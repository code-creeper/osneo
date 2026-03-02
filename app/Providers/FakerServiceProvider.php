<?php

namespace App\Providers;

use App\Faker\DatetimeFaker;
use Faker\{Factory, Generator, Provider\Fakecar};
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Generator::class, function () {
            $faker = Factory::create();
            $faker->addProvider(new Fakecar($faker));
            $faker->addProvider(new DatetimeFaker($faker));
            return $faker;
        });
    }
}
