<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getValueAttribute($value): mixed
    {
        $name = $this->attributes['name'];
        $type = config('preferences')[$name]['type'] ?? 'mixed';

        return match ($type) {
            'array' => json_decode($value),
            default => $value,
        };
    }

    public function setValueAttribute($value): void
    {
        $name = $this->attributes['name'];
        $type = config('preferences')[$name]['type'] ?? 'mixed';

        $this->attributes['value'] = match ($type) {
            'array' => json_encode($value),
            default => $value,
        };
    }
}
