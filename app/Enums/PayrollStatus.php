<?php

namespace App\Enums;

enum PayrollStatus: int
{
    case OPEN_ISSUES = 1;
    case READY = 2;
    case IN_PROGRESS = 3;
    case COMPLETED = 4;

    public function label(): string
    {
        return match($this) {
            self::OPEN_ISSUES => 'Open Issues',
            self::READY => 'Ready',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
        };
    }
}
