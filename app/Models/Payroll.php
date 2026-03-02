<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class Payroll extends Model
{
    use HasFactory;

    use SchemalessAttributesTrait;

    protected $guarded = ['id'];

    protected $schemalessAttributes = [
        'overtimes',
        'surcharges',
        'vacation'
    ];

    protected $casts = [
        'date' => 'date',
        'leaves' => 'array',
        'status' => PayrollStatus::class
    ];

    public function scopeWithOvertimes(): Builder
    {
        return $this->overtimes->modelScope();
    }

    public function scopeWithSurcharges(): Builder
    {
        return $this->surcharges->modelScope();
    }

    public function scopeWithVacation(): Builder
    {
        return $this->vacation->modelScope();
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

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function salary(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->hourly_rate * $this->working_hours
        );
    }

    public function leavePayout(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->hourly_rate * $this->leaves_balance * $this->target_hours
        );
    }

    public function grossTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = $this->salary;
                $total += $this->leavePayout;

                foreach ($this->overtimes as $overtime) {
                    $total += $overtime['hours'] * $overtime['hourly_rate'];
                }

                foreach ($this->surcharges as $surcharge) {
                    if ($surcharge['tax'] == 'gross') {
                        $total += $surcharge['amount'];
                    }
                }

                return $total;
            }
        );
    }

    public function netTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = 0;

                foreach ($this->surcharges as $surcharge) {
                    if ($surcharge['tax'] == 'net') {
                        $total += $surcharge['amount'];
                    }
                }

                return $total;
            }
        );
    }

    public function month(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->format('Y-m')
        );
    }

    public function year(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->year
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    // process the payroll to make the payouts
    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function process(): void
    {
        DB::beginTransaction();
        try {
            $this->user->createLeaveTransaction([
                'transacted_by' => auth()->id(),
                'amount' => 0 - $this->leaves_balance,
                'comments' => 'Leaves payout',
                'transacted_on' => $this->date,
            ]);

            foreach ($this->overtimes as $overtime) {
                $this->user->payouts()->create([
                    'logged_by' => auth()->id(),
                    'date' => $this->date,
                    'duration' => $overtime['hours'] * 60,
                    'comments' => 'Payment of overtime from '.$this->date->format('F Y'),
                    'payout' => 1,
                ]);
            }

            $this->update([
                'status' => PayrollStatus::COMPLETED,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

        DB::commit();
    }

}
