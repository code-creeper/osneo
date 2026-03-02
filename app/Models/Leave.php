<?php

namespace App\Models;

use App\Contracts\ActivityFormatter;
use App\Enums\ModificationType;
use App\Jobs\UpdateAttendanceSummariesJob;
use App\Contracts\Modifiable;
use App\Helpers\LeavesHelper;
use App\Observers\LeaveObserver;
use App\ActivityFormatters\LeaveActivityFormatter;
use App\Traits\HasActiveUser;
use App\Traits\HasLeaveType;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[ObservedBy(LeaveObserver::class)]
class Leave extends Model implements Modifiable, ActivityFormatter
{
    use HasFactory;
    use LogsActivity;
    use HasActiveUser;
    use HasLeaveType;
    use SoftDeletes;
    use LeaveActivityFormatter;

    protected $guarded = ['id' ];

    protected $casts = [
        'starts_on' => 'datetime',
        'ends_on' => 'datetime',
        'user_id' => 'int',
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

    public function leaveDays(): HasMany
    {
        return $this->hasMany(LeaveDay::class);
    }

    public function modifications(): MorphMany
    {
        return $this->morphMany(Modification::class, 'modifiable');
    }

    public function pendingModification(): MorphOne
    {
        return $this->morphOne(Modification::class, 'modifiable')->pending()->latestOfMany();
    }

    public function leaveTransactions(): HasMany
    {
        return $this->hasMany(LeaveTransaction::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function claim(): HasOne
    {
        return $this->hasOne(InsuranceClaim::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getStatusAttribute():string
    {
        if ($this->approved_by) {
            return __('Approved');
        }

        if ($this->rejected_by) {
            return __('Rejected');
        }

        return __('Pending');
    }

    public function getStartsOnDateAttribute(): string
    {
        return $this->starts_on->format(config('dates.default'));
    }

    public function getEndsOnDateAttribute(): string
    {
        return $this->ends_on->format(config('dates.default'));
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public static function toEventsArray(Collection $collection): array
    {
        return $collection->map(function ($leave) {
            return [
                'id' => 'l'.$leave->id,
                'resourceId' => $leave->user_id,
                'groupId' => $leave->reason_id,
                'title' => $leave->reason->name,
                'backgroundColor' => $leave->reason->color,
                'allDay' => true,
                'display' => 'background',
                'start' => $leave->starts_on,
                'end' => $leave->ends_on->addDay(),
            ];
        })->toArray();
    }

    public function isPending(): bool
    {
        return $this->approved_by == null && $this->rejected_by == null;
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }

    public function isRejected(): bool
    {
        return $this->rejected_by !== null;
    }

    public function getFormattedChanges(Modification $modification): array
    {
        $changes = array();

        if ($modification->type == ModificationType::Delete) {
            $leave = $modification->modifiable;
            $changes['starts_on'] = ['label' => 'Starts On', 'source' => $leave->starts_on->toDateString(),];
            $changes['ends_on'] = ['label' => 'Ends On', 'source' => $leave->ends_on->toDateString(),];
            $changes['days'] = ['label' => 'Days', 'source' => $leave->days,];
            $changes['reason'] = ['label' => 'Reason', 'source' => $leave->reason->name,];

            return $changes;
        }

        foreach ($modification->data as $column => $data) {
            $changes[$column]['label'] = str($column)->replace('_', ' ')->title()->value();

            switch ($column) {
                case 'reason_id':
                    $changes[$column]['source'] = LeaveReason::find($modification->source[$column])->name;
                    $changes[$column]['data'] = LeaveReason::find($data)->name;
                    $changes[$column]['label'] = 'Reason';
                    break;
                default:
                    $changes[$column]['source'] = $modification->source[$column];
                    $changes[$column]['data'] = $modification->data[$column];
            }
        }

        return $changes;
    }

    public function createModification(array $changes, ModificationType $type = ModificationType::Edit): bool
    {
        $modification = $this->modifications()->make([
            'type' => $type,
            'user_id' => auth()->id(),
            'comments' => $changes['comments'] ?? null,
        ]);

        if ($type == ModificationType::Delete || $type == ModificationType::Restore) {
            return $modification->save();
        }

        $originalStartsOn = $this->getOriginal('starts_on')->toDateString();
        $modifiedStartsOn = Carbon::parse($changes['starts_on'])->setDefaultTz()->toDateString();

        if ($originalStartsOn != $modifiedStartsOn) {
            $modification->source->starts_on = $originalStartsOn;
            $modification->data->starts_on = $modifiedStartsOn;
        }

        $originalEndsOn = $this->getOriginal('ends_on')->toDateString();
        $modifiedEndsOn = Carbon::parse($changes['ends_on'])->setDefaultTz()->toDateString();

        if ($originalEndsOn != $modifiedEndsOn) {
            $modification->source->ends_on = $originalEndsOn;
            $modification->data->ends_on = $modifiedEndsOn;
        }

        if (isset($changes['reason_id']) && $changes['reason_id'] != $this->getOriginal('reason_id')) {
            $modification->source->reason_id = $this->getOriginal('reason_id');
            $modification->data->reason_id = $changes['reason_id'];
        }

        if ($modification->data->isEmpty()) {
            return false;
        }

        return $modification->save();
    }

    public function applyChanges(Modification $modification): void
    {
        $data = $modification->data->only([
            'starts_on', 'ends_on', 'reason_id',
        ])->toArray();

        // if starts_on or ends_on is not set ( changed ), we get the original value from leave
        $starts_on = $data['starts_on'] ?? $modification->modifiable->starts_on;
        $ends_on = $data['ends_on'] ?? $modification->modifiable->ends_on;

        $data['days'] = LeavesHelper::getLeaveDates($starts_on, $ends_on, $this->user)->count();

        $modification->modifiable->update($data);
    }

    public function applyDeletion(): void
    {
        $this->delete();
    }

    public function isCreatedByAdmin(): bool
    {
        return $this->created_by != null;
    }

    public function updateAttendanceSummary(): void
    {
        // update summary for each leave day
        UpdateAttendanceSummariesJob::dispatch($this->starts_on, $this->ends_on, $this->user_id);

        if ($this->wasRecentlyCreated) {
            return;
        }

        if ($this->isClean('starts_on', 'ends_on')) {
            return;
        }

        // update summary for old leave days
        UpdateAttendanceSummariesJob::dispatch(
            Carbon::parse($this->original['starts_on']),
            Carbon::parse($this->original['ends_on']),
            $this->user_id
        );
    }

    public function reject(): void
    {
        $this->update([
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approve(): void
    {
        $this->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
        ]);
    }

    public function createLeaveDays(): void
    {
        $leaveDates = LeavesHelper::getLeaveDates($this->starts_on, $this->ends_on, $this->user);

        $leaveDates->forEach(fn($date) => $this->leaveDays()->updateOrCreate(['date' => $date],[
            'reason_id' => $this->reason_id,
            'user_id' => $this->user_id,
            'date' => $date,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all leaves')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }

    public function scopeStatus(Builder $builder, $status = null)
    {
        $statuses = ['pending', 'approved', 'rejected'];

        if ( ! in_array($status, $statuses)) {
            return $builder;
        }

        return $builder->{$status}();
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->whereNull('approved_by')->whereNull('rejected_by');
    }

    public function scopeApproved($builder)
    {
        return $builder->whereNotNull('approved_by');
    }

    public function scopeRejected(Builder $builder): Builder
    {
        return $builder->whereNotNull('rejected_by');
    }

    public function scopeOngoing(Builder $builder, $date = null)
    {
        if ($date instanceof Carbon) {
            $date = $date->toDateString();
        }

        return $builder->started($date)->future('ends_on', $date);
    }

    public function scopeStarted(Builder $builder, $date = null): Builder
    {
        if ($date instanceof Carbon) {
            $date = $date->toDateString();
        }

        return $builder->past('starts_on', $date);
    }

    public function scopeEnded(Builder $builder, $date = null): Builder
    {
        if ($date instanceof Carbon) {
            $date = $date->toDateString();
        }

        return $builder->past('ends_on', $date, true);
    }

    // check if a leave overlaps with the given dates
    public function scopeOverLapping(Builder $builder, $starts_on, $ends_on): Builder
    {
        return $builder->past('starts_on', $ends_on)->future('ends_on', $starts_on);
    }

    public function scopeForMonth(Builder $query, $month, $year): Builder
    {
        return $query->where(fn(Builder $query) => $query
            ->whereMonthOfYear('starts_on', $month, $year)
            ->orWhereMonthOfYear('ends_on', $month, $year)
        );
    }

    // where starts_on or ends_on is between given dates
    public function scopeBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->where(fn(Builder $query) => $query
            ->whereBetween('starts_on', [$startDate, $endDate])
            ->orWhereBetween('ends_on', [$startDate, $endDate])
        );
    }
}
