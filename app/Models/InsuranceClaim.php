<?php

namespace App\Models;

use App\Enums\InsuranceClaimStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class InsuranceClaim extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => InsuranceClaimStatus::class,
        'last_requested_on' => 'date',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function (self $claim) {
            $claim->generateClaimNumber();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function generateClaimNumber(): void
    {
        $this->updateQuietly([
            'claim_number' => str('SL-')
                ->append($this->leave_id)
                ->append('-')
                ->append($this->id)
                ->append('-')
                ->append(Str::random(4)),
        ]);
    }

    public function acceptsDocument(): bool
    {
        return in_array($this->status, [
            InsuranceClaimStatus::OPEN,
            InsuranceClaimStatus::WAITING,
        ]);
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, [
            InsuranceClaimStatus::REJECTED,
            InsuranceClaimStatus::DONE,
        ]);
    }

    public function isWaiting(): bool
    {
        return $this->status == InsuranceClaimStatus::WAITING;
    }

    public function isOpen(): bool
    {
        return $this->status == InsuranceClaimStatus::OPEN;
    }

    public function isRejected(): bool
    {
        return $this->status == InsuranceClaimStatus::REJECTED;
    }

    public function isConfirmed(): bool
    {
        return $this->status == InsuranceClaimStatus::CONFIRMED;
    }

    public function isUnconfirmed(): bool
    {
        return $this->status == InsuranceClaimStatus::UNCONFIRMED;
    }
}
