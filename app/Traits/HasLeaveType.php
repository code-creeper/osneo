<?php

namespace App\Traits;

use App\Models\LeaveReason;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasLeaveType
{
    public function reason(): BelongsTo
    {
        return $this->belongsTo(LeaveReason::class, 'reason_id')->withDefault();
    }

    public function isDeductible(LeaveReason|int $reason = null): bool
    {
        $reason = $reason ?? $this->reason;

        if (is_int($reason)) {
            $reason = LeaveReason::findOr($reason,
                fn() => throw new Exception("Invalid leave reason")
            );
        }

        return $reason->deductible;
    }

    public function scopePaid(Builder $builder): Builder
    {
        return $builder->whereHas('reason',
            fn($builder) => $builder->wherePaid(1)
        );
    }

    public function scopeDeductible(Builder $builder): Builder
    {
        return $builder->whereHas('reason',
            fn($builder) => $builder->whereDeductible(1)
        );
    }

    public function scopeUnpaid(Builder $builder): Builder
    {
        return $builder->whereHas('reason',
            fn($builder) => $builder->wherePaid(0)
        );
    }

    //todo:: remove - we do not use offsite work anymore, we have paid or unpaid leaves
    // offsite_work can be a paid leave with deductible 0

    public function scopeOffsiteWork(Builder $builder): Builder
    {
        return $builder->whereHas('reason',
            fn($builder) => $builder->where(
                'extra_attributes->leave_category', 'offsite_work'
            )
        );
    }

    /**
     * @throws Exception
     */
    public function scopeType(Builder $builder, $type): Builder
    {
        $types = [
            'sick_leave' => 1,
            'child_sick_leave' => 2
        ];

        if (! in_array($type, array_keys($types))){
            throw new Exception('Invalid leave type');
        }

        return $builder->where('reason_id', $types[$type]);
    }
}
