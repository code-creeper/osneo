<?php


namespace App\Helpers;

use App\Models\Constant;
use Illuminate\Support\Collection;

class ConstantHelper
{
    public static function damageStatuses(): Collection
    {
        return Constant::group('damage_statuses')->get();
    }

    public static function getDefaultDamageStatus(): ?int
    {
        return Constant::group('damage_statuses')->first()?->id;
    }
}
