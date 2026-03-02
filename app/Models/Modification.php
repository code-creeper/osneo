<?php

namespace App\Models;

use App\ActivityFormatters\ModificationActivityFormatter;
use App\Contracts\ActivityFormatter;
use App\Contracts\Modifiable;
use App\Enums\ModificationType;
use App\Traits\HasActiveUser;
use App\Traits\LogsActivity;
use App\Traits\SerializeDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class Modification extends Model implements ActivityFormatter
{
    use HasFactory, SchemalessAttributesTrait, SerializeDate, LogsActivity;
    use HasActiveUser;
    use ModificationActivityFormatter;

    protected $guarded = ['id'];

    protected $schemalessAttributes = ['source', 'data'];

    protected $casts = [
        'type' => ModificationType::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function modifiable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    // get new instance of modifiable class
    public function getNewModifiable(): Modifiable
    {
        return new $this->modifiable_type;
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getChangesCountAttribute(): int
    {
        return $this->data->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithSource(): Builder
    {
        return $this->source->modelScope();
    }

    public function scopeWithChanges(): Builder
    {
        return $this->data->modelScope();
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->whereNull('approved_at');
    }

    public function scopeApproved(Builder $builder): Builder
    {
        return $builder->whereNotNull('approved_at');
    }

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all modifications')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }
}
