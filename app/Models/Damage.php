<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Constant::class, 'status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all damages')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }

}
