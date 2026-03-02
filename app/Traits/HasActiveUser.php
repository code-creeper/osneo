<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveUser
{
    public function scopeOfActiveUsers(Builder $query): void
    {
        $query->whereHas('user', function ($q) {
            $q->active();
        });
    }
}
