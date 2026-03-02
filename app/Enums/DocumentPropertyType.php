<?php

namespace App\Enums;

enum DocumentPropertyType: string
{
    case TEXT = 'Text';
    case DATE = 'Date';
    case INTEGER = 'Integer';
    case Ticket = 'Ticket';
    case CONTACT_NUMBER = 'Contact Number';
    case CONTACT_NAME = 'Contact Name';

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->value;
        }
        return $array;
    }
}
