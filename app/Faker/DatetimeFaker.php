<?php

namespace App\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Carbon;

class DatetimeFaker extends Base
{
    public function futureDateTimeThisYear($until = 'last day of december', string $timezone = null): \DateTime
    {
        return $this->generator->dateTimeBetween('now', $until, $timezone);
    }

    public function dateTimeThisHour(): Carbon
    {
        return now()->startOfHour()->addMinutes($this->generator->numberBetween(0, 59));
    }
}
