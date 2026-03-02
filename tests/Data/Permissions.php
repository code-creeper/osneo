<?php

namespace Tests\Data;

class Permissions
{
    public static function allPermissions()
    {
        return config('permissions');
    }

    public static function attendanceBasic(): array
    {
        return [
            'view own attendance',
            'create attendance',
            'create manual attendance',
            'edit own attendance',
            'delete own attendance',
            'restore own attendance',
        ];
    }

    public static function attendanceWithoutApproval(): array
    {
        return array_merge(self::attendanceBasic(), [
            'create manual attendance without approval',
            'edit attendance without approval',
            'delete attendance without approval',
            'restore attendance without approval',
        ]);
    }

    public static function leaveBasic(): array
    {
        return [
            'view own leaves',
            'create leaves',
            'edit own leaves',
            'delete own leaves',
        ];
    }

    public static function leavePreApproval(): array
    {
        return array_merge(self::leaveBasic(), [
            'create pre-approved leaves',
            'create pre-approved leaves for all',
        ]);
    }

    public static function leaveEditWithoutApproval(): array
    {
        return array_merge(self::leaveBasic(), [
            'edit leaves without approval',
        ]);
    }

    public static function leaveDeleteWithoutApproval(): array
    {
        return array_merge(self::leaveBasic(), [
            'delete leaves without approval',
        ]);
    }

    public static function leaveAdmin(): array
    {
        return [
            'view all leaves',
            'view own leaves',
            'create leaves',
            'create leaves for all',
            'create pre-approved leaves',
            'create pre-approved leaves for all',
            'edit any leaves',
            'edit own leaves',
            'edit leaves without approval',
            'delete any leaves',
            'delete own leaves',
            'delete leaves without approval',
            'approve leaves',
            'reject leaves',
            'tag leaves',
            'ignore leaves balance',
        ];
    }
}
