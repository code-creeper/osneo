<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceHistory::class)
            ->orderByDesc('id');
    }

    public function condition(): HasOne
    {
        return $this->hasOne(VehicleMaintenanceHistory::class)->latestOfMany()
            ->withDefault([
                'mileage' => 0,
            ]);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id')->withDefault([
            'first_name' => __('Not Occupied')
        ]);
    }

    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }

    public function driverHistories(): HasMany
    {
        return $this->hasMany(VehicleDriverHistory::class)->orderByDesc('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute(): string
    {
        return "$this->license_plate - $this->manufacturer $this->model";
    }

    public function getLastUpdatedOnAttribute(): ?string
    {
        return $this->condition->created_at;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function updateDriver($driverId): void
    {
        $this->update(['driver_id' => $driverId]);

        $this->driverHistories()->create([
            'driver_id' => $driverId,
        ]);
    }

    public function removeDriver(): void
    {
        $this->update(['driver_id' => null]);
    }

}
