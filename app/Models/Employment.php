<?php

namespace App\Models;

use App\Jobs\UpdateAttendanceSummariesJob;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'off_days' => 'array',
        'started_on' => 'datetime',
        'ended_on' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function (self $employment) {
            $employment->updateAttendanceSummary();
        });

        self::updated(function (self $employment) {
            //cache()->tags("user_{$employment->user_id}_employments")->flush();
            $employment->updateAttendanceSummary();
        });

        self::deleted(function (self $employment) {
            //cache()->tags("user_{$employment->user_id}_employments")->flush();
            $employment->updateAttendanceSummary();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): Attribute
    {
        $ended_on = ($this->ended_on?->date()) ?? "Present";
        $started_on = $this->started_on?->date();
        return Attribute::make(
            get: fn() => "$started_on - $ended_on",
        );
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        $date = Carbon::parse($date);

        return $query->where(fn(Builder $query) => $query
            ->where('started_on', '<=', $date)
            ->where(fn($query) => $query
                ->where('ended_on', '>=', $date)
                ->orWhereNull('ended_on')
            )
        );
    }

    public function updateAttendanceSummary(): void
    {
        // update summary for employment period
        UpdateAttendanceSummariesJob::dispatch($this->started_on, $this->ended_on ?? now(), $this->user_id);

        if ($this->wasRecentlyCreated) {
            return;
        }

        if ($this->isClean('started_on', 'ended_on')) {
            return;
        }

        // update summary for old employment

        UpdateAttendanceSummariesJob::dispatch(
            Carbon::parse($this->original['started_on']),
            $this->original['ended_on'] ? Carbon::parse($this->original['ended_on']) : now(),
            $this->user_id
        );
    }

}
