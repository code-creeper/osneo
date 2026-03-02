<?php

namespace App\Models;

use App\Enums\AttendanceAction;
use App\Notifications\AttendanceActionTaken;
use App\Traits\HasActiveUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualEntry extends Model
{
    use HasFactory, HasActiveUser;

    protected $fillable = [
        'user_id', 'duration', 'logged_by', 'date', 'type', 'comments', 'payout',
    ];

    protected $casts = [
        'date' => 'datetime',
        'payout' => 'bool'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function (self $entry) {
            $entry->user->updateAttendanceSummary($entry->date);
        });

        static::updated(function (self $entry) {
            $entry->user->updateAttendanceSummary($entry->date);
        });

        static::deleted(function (self $entry) {
            $entry->user->updateAttendanceSummary($entry->date);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by')
            ->withDefault([
                'first_name' => 'System',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    // get only minutes part from hh:mm
    public function getMinutesAttribute(): int
    {
        return fmod($this->duration, 60);
    }

    // get only hours part from hh:mm
    public function getHoursAttribute(): int
    {
        return floor($this->duration/60);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeBreak(Builder $builder): Builder
    {
        return $builder->where('duration', '<', 0)->where('payout', 0);
    }

    public function scopeAttendance(Builder $builder): Builder
    {
        return $builder->where('duration', '>', 0)->where('payout', 0);
    }

    public function scopePayout(Builder $builder): Builder
    {
        return $builder->where('payout', 1);
    }

    //todo:: remove
    public function isBreak(): bool
    {
        return $this->duration < 0;
    }
}
