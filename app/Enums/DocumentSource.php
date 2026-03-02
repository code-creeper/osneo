<?php

namespace App\Enums;

enum DocumentSource: string
{
    case KRE = 'Kreditor';
    case DEB = 'Debitor';

    public static function toArray(): array
    {
        return array_map(fn(self $case) => $case->name, DocumentSource::cases());
    }
}
