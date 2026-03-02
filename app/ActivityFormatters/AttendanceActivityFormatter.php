<?php

namespace App\ActivityFormatters;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;

trait AttendanceActivityFormatter
{
    public function getActivityIcon(Activity $activity): string
    {
        if ($activity->event == 'created'){
            return "fa fa-folder-upload bg-info-lighten text-info";
        }

        return "fa fa-refresh bg-primary-lighten text-primary";
    }

    public function formatActivity(Activity $activity): array
    {
        $changes = [];

        if ($activity->event == 'created'){
            $changes[] =  __(":model created by :user", [
                'model' => class_basename($this),
                'user' => $activity->causer->name
            ]);

            return $changes;
        }

        if ($activity->event !== 'updated'){
            return $changes;
        }

        foreach ($activity->changedAttributes() as $attribute => $newValue){
            $oldValue = $activity->oldAttributeValue($attribute);
            $icon = '<i class="fal fa-long-arrow-right mx-2"></i>';
            $attributeName = $activity->getAttributeName($attribute);

            $text = "<strong>$attributeName</strong> :  &Tab;";

            switch ($attribute){
                default:
                    $text .= "$oldValue $icon $newValue";
            }

            $changes[] = $text;
        }

        return $changes;
    }

    public function formatActivityAttribute(string $attribute, mixed $value = null): string|null
    {
        return match ($attribute) {
            'created_by',
            'updated_by',
            'user_id' => User::find($value)?->name,
            'checkin',
            'checkout' => Carbon::parse($value)->format('H:i'),
            'date' => Carbon::parse($value)->date(),
            default => $value,
        };
    }

    public function getActivityAttributeName(string $attribute, string $default): string
    {
        return match ($attribute) {
            'user_id' => 'User',
            default => $default,
        };
    }
}
