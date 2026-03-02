<?php

namespace App\Models;

use App\Contracts\Modifiable;
use App\Contracts\ActivityFormatter;
use App\Enums\AttendanceAction;
use App\Enums\ModificationType;
use App\Notifications\AttendanceActionTaken;
use App\ActivityFormatters\AttendanceActivityFormatter;
use App\Traits\LogsActivity;
use App\Traits\SerializeDate;
use App\Traits\Sortable;
use Carbon\Carbon;
use DateTimeInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class Attendance extends Model implements Modifiable, ActivityFormatter
{
    use HasFactory,
        SoftDeletes,
        SerializeDate,
        LogsActivity,
        Sortable;
    use AttendanceActivityFormatter;

    protected $guarded = ['id'];

    protected $casts = [
        'checkin' => 'datetime',
        'checkout' => 'datetime',
        'date' => 'date',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function (self $attendance) {
            $attendance->updateAttendanceDuration();

            if ($attendance->isCreatedByAdmin()){
                $attendance->user->notify(new AttendanceActionTaken($attendance, AttendanceAction::Created));
            }
        });

        static::updated(function (self $attendance) {
            $attendance->updateAttendanceDuration();

            if ($attendance->isUpdatedByAdmin()){
                $attendance->user->notify(new AttendanceActionTaken($attendance, AttendanceAction::Updated));
            }

            //todo:: handle properly
            /*if ($attendance->duration !== null && $attendance->duration < 1) {
                $this->forceDelete();
            }*/
        });

        static::deleted(function (self $attendance) {
            //$attendance->updateAttendanceDuration();
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

    public function modifications(): MorphMany
    {
        return $this->morphMany(Modification::class, 'modifiable');
    }

    public function pendingModification(): MorphOne
    {
        return $this->morphOne(Modification::class, 'modifiable')->pending()->latestOfMany();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRelevant(Builder $builder): Builder
    {
        if (user()->can('view all attendance')) {
            return $builder;
        }

        return $builder->where('user_id', auth()->id());
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->whereNull('checkout')->where('date', today()->toDateString());
    }

    public function scopeCheckedOut(Builder $builder): Builder
    {
        return $builder->whereNotNull('checkout');
    }

    public function scopeToday(Builder $builder): Builder
    {
        return $builder->where('date', today()->toDateString());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    public function checkin(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? Carbon::parse($value)->floorMinute() : null,
        );
    }

    public function checkout(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? Carbon::parse($value)->floorMinute() : null,
        );
    }

    public function formattedDuration(): Attribute
    {
        return Attribute::make(
            get: fn () => formatMins($this->duration, true),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public static function toEventsArray(Collection $collection): array
    {
        return $collection->map(function ($attendance) {
            return [
                'id' => 'a'.$attendance->id,
                'resourceId' => $attendance->user_id,
                'start' => $attendance->checkin,
                'end' => $attendance->checkout,
            ];
        })->toArray();
    }

    public function hasStarted(): bool
    {
        return $this->checkin && $this->checkout == null;
    }

    public function isActive(): bool
    {
        return $this->hasStarted() && $this->date->toDateString() == today()->toDateString();
    }

    public function getFormattedChanges(Modification $modification): array
    {
        $changes = array();

        $format = config('dates.attendance.datetime');

        if($modification->type == ModificationType::Create){
            $changes['checkin'] = [
                'label' => 'Checkin',
                'source' => Carbon::parse($modification->source->checkin)->setDefaultTz()->format($format),
            ];
            $changes['checkout'] = [
                'label' => 'Checkout',
                'source' => Carbon::parse($modification->source->checkout)->setDefaultTz()->format($format),
            ];

            return $changes;
        }

        if ($modification->type == ModificationType::Delete || $modification->type == ModificationType::Restore) {
            $attendance = $this;
            $changes['checkin'] = [
                'label' => 'Checkin',
                'source' => $attendance->checkin->format($format),
            ];
            $changes['checkout'] = [
                'label' => 'Checkout',
                'source' => $attendance->checkout?->format($format),
            ];

            return $changes;
        }

        foreach ($modification->data as $column => $data) {
            $changes[$column]['label'] = ucfirst(str_replace('_', ' ', $column));

            switch ($column) {
                case 'checkout':
                case 'checkin':
                    $changes[$column]['source'] = Carbon::parse($modification->source[$column])->setDefaultTz()->format($format);
                    $changes[$column]['data'] = Carbon::parse($modification->data[$column])->setDefaultTz()->format($format);
                    break;
                default:
                    $changes[$column]['source'] = $modification->source[$column];
                    $changes[$column]['data'] = $modification->data[$column];
                    break;
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

        $checkin = isset($changes['checkin']) ? $changes['checkin']->roundMinute() : null;
        $checkout = isset($changes['checkout']) ? $changes['checkout']->roundMinute() : null;

        if ($checkin && $checkin != $this->getOriginal('checkin')) {
            $modification->source->checkin = $this->getOriginal('checkin');
            $modification->data->checkin = $checkin;
        }

        if ($checkout && $checkout != $this->getOriginal('checkout')) {
            $modification->source->checkout = $this->getOriginal('checkout');
            $modification->data->checkout = $checkout;
        }

        if ($modification->data->isEmpty()) {
            return false;
        }


        return $modification->save();
    }

    public function applyChanges(Modification $modification): void
    {
        if ($modification->data->checkin) {
            $this->checkin = Carbon::parse($modification->data->checkin)->setDefaultTz();
        }

        if ($modification->data->checkout) {
            $this->checkout = Carbon::parse($modification->data->checkout)->setDefaultTz();
        }

        if ($modification->data->created_by) {
            $this->created_by = $modification->data->created_by;
        }

        $this->save();
    }

    public function applyDeletion(): void
    {
        $this->delete();
    }

    public function applyRestoration(): void
    {
        $this->restore();
    }

    public static function requestCreation(array $data): void
    {
        //todo:: some validation
        $modification = Modification::create([
            'modifiable_type' => self::class,
            'modifiable_id' => 0,
            'type' => ModificationType::Create,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'comments' => $data['comments'] ?? '',
        ]);

        $modification->source->date = $data['date'];
        $modification->source->checkin = $data['checkin']->roundMinute();
        $modification->source->checkout = $data['checkout']->roundMinute();

        $modification->save();
    }

    public function applyCreation(Modification $modification): void
    {
        $source = $modification->source;

        $modification->user->attendances()->create([
            'date' => $source->date,
            'checkin' => Carbon::parse($source->checkin)->setDefaultTz(),
            'checkout' => Carbon::parse($source->checkout)->setDefaultTz(),
        ]);
    }

    public function updateAttendanceDuration(): void
    {
        if ($this->checkin == null || $this->checkout == null) {
            return;
        }

        $this->updateQuietly([
            'duration' => $this->checkin->diffInMinutes($this->checkout),
        ]);

        $this->user?->updateAttendanceSummary($this->checkin);
    }

    public function isCreatedByAdmin(): bool
    {
        return $this->created_by != null && $this->created_by != $this->user_id;
    }

    public function isUpdatedByAdmin(): bool
    {
        return $this->updated_by != null && $this->updated_by != $this->user_id;
    }
}
