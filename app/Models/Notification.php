<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return parent::__get($key);
    }

    public function getIconAttribute()
    {
        return $this->data['icon'] ?? 'fal fa-bell';
    }
}
