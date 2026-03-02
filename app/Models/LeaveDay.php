<?php

namespace App\Models;

use App\Traits\HasLeaveType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\PowerJoins\PowerJoins;

class LeaveDay extends Model
{
    use HasLeaveType;
    use PowerJoins;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(LeaveReason::class);
    }
}
