<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use \Spatie\Activitylog\Traits\LogsActivity as BaseLogsActivity;

trait LogsActivity
{
    use BaseLogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $model = class_basename(self::class);
        return LogOptions::defaults()
            ->useLogName('crud')
            ->logOnly($this->getLoggableAttributes())
            ->dontLogIfAttributesChangedOnly($this->getNonLoggableAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => strtoupper($eventName). " $model");
    }

    public function getLoggableAttributes(): array
    {
        $nonLoggableAttributes = $this->getNonLoggableAttributes();
        $loggable =  $this->loggable ?? array_keys($this->getAttributes());

        return array_values(array_diff($loggable, $nonLoggableAttributes));
    }

    public function getNonLoggableAttributes(): array
    {
        $ignored = [
            'created_at',
            'updated_at'
        ];

        return array_merge($this->nonLoggable ?? [], $ignored, $this->guarded);
    }
}
