<?php

namespace App\Enums;

enum ModificationType: string
{
    case Create = 'create';
    case Edit = 'edit';
    case Delete = 'delete';
    case Restore = 'restore';

    public function color(): string
    {
        return match($this) {
            self::Create => 'success',
            self::Edit => 'warning',
            self::Delete => 'danger',
            self::Restore => 'info',
        };
    }
}
