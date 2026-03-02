<?php


namespace App\Contracts;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface ActivityFormatter
{
    public function activities(): MorphMany;

    public function getActivityIcon(Activity $activity): string;

    public function formatActivity(Activity $activity): array;

    public function formatActivityAttribute(string $attribute, mixed $value = null): null|string;

    public function getActivityAttributeName(string $attribute, string $default): string;
}
