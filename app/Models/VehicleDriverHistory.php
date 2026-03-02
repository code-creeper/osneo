<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDriverHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = ['taken_at'];

    protected $casts = [
        'handed_over_at' => 'datetime',
    ];

    const CREATED_AT  = 'taken_at';
    const UPDATED_AT = null;

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
