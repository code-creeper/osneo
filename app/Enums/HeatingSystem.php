<?php

namespace App\Enums;

enum HeatingSystem: string
{
    case HEAT_PUMP = 'Heat Pump';
    case GAS = 'Gas';
    case OIL = 'Oil';
    case FUEL_CELL = 'Fuel Cell';
    case PALLET = 'Pallet Number';
    case DISTRICT_HEATING = 'District Heating';

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->value;
        }
        return $array;
    }
}
