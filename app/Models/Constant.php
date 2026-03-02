<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class Constant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $casts = [
        'fields' => SchemalessAttributes::class,
    ];

    public function __toString()
    {
        return $this->value ?? '';
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }

    public function scopeGroup(Builder $query, $group): Builder
    {
        return $query->whereGroup($group);
    }
}
