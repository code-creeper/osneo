<?php

namespace App\Models;

use App\ActivityFormatters\LeaveTransactionActivityFormatter;
use App\Contracts\ActivityFormatter;
use App\Traits\LogsActivity;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTransaction extends Model implements ActivityFormatter
{
    use HasFactory, Sortable, LogsActivity;
    use LeaveTransactionActivityFormatter;

    protected $guarded = ['id'];

    protected $casts = [
        'transacted_on' => 'date'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transacted_by')
            ->withDefault([
                'first_name' => 'System',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all leave transactions')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }
}
