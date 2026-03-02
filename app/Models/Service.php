<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Service extends Model
{
    use HasFactory;

    use SchemalessAttributesTrait;
    use HasJsonRelationships;

    protected $guarded = ['id'];

    protected $schemalessAttributes = [
        'sizes',
    ];

    public $timestamps = false;

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithSizes(): Builder
    {
        return $this->sizes->modelScope();
    }
}
