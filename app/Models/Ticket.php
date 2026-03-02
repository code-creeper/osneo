<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    protected function setNumberAttribute($value): void
    {
        $this->attributes['number'] = str($value)->trim()->value();
    }
}
