<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenanceHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'craftsman_license_expiry' => 'date',
        'first_aid_kit_expiry' => 'date',
        'next_maintenance_date' => 'date',
        'mot_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getConstantText($attribute): string
    {
        $constants = array_merge(
            config('constants.yes_no'),
            config('constants.tank_levels'),
            config('constants.vehicle_conditions'),
        );

        $constant = $this->{$attribute};

        if (\Arr::has($constants, $constant)) {
            return __($constants[$constant]);
        }

        return '-';
    }

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all vehicle histories')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }
}
