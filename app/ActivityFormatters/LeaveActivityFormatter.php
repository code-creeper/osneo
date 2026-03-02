<?php

namespace App\ActivityFormatters;

use App\Models\Activity;
use App\Models\LeaveReason;
use App\Models\User;
use Carbon\Carbon;

trait LeaveActivityFormatter
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
                case 'approved_by':
                    $text = "Leave Approved by <strong>{$activity->causer->name}</strong>";
                    break;
                case 'reason_id':
                    $text = "Updated leave reason $oldValue $icon $newValue";
                    break;
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
            'starts_on',
            'ends_on' => Carbon::parse($value)->date(),
            'reason_id' => LeaveReason::find($value)->name,
            'user_id',
            'created_by',
            'rejected_by',
            'approved_by' => User::find($value)?->name,
            default => $value,
        };
    }

    public function getActivityAttributeName(string $attribute, $default): string
    {
        return match ($attribute) {
            'reason_id' => "Reason",
            'user_id' => "User",
            default => $default,
        };
    }
}
