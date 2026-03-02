<?php


namespace App\Traits;

use DateTimeInterface;

trait SerializeDate
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->toISOString(true);
    }
}
