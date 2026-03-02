<?php

namespace Database\Factories;

use App\Helpers\LeavesHelper;
use App\Models\Employment;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        return [
            'created_by' => null,

            // we do not seed leaves for far future, so it does remain in the employment period
            // which we set to start few months in the past and ends few months in future
            'starts_on' => now()->next('monday'),
            'ends_on' => fn($attributes) => Carbon::parse($attributes['starts_on'])->addDays($this->faker->numberBetween(1, 10)),

            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'notes' => $this->faker->word(),
            'remarks' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'reason_id' => LeaveReason::factory(),
        ];
    }

    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            $approvedBy = User::factory()->create()->id;
            $approvedAt = Carbon::now();

            return [
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ];
        });
    }

    public function paid(): self
    {
        return $this->state(fn($attrs) => [
            'reason_id' => LeaveReason::factory()->paid(),
        ]);
    }

    public function deductible(): self
    {
        return $this->state(fn($attrs) => [
            'reason_id' => LeaveReason::factory()->deductible(),
        ]);
    }

    public function vacation(): self
    {
        return $this->state(fn($attrs) => [
            'reason_id' => LeaveReason::factory()->vacation(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(function (array $attributes) {
            $rejectedBy = User::factory()->create()->id;
            $rejectedAt = Carbon::now();

            return [
                'rejected_by' => $rejectedBy,
                'rejected_at' => $rejectedAt,
            ];
        });
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Leave $leave) {
            $leave->days = LeavesHelper::getLeaveDates($leave->starts_on, $leave->ends_on, $leave->user)->count();
            $leave->save();
        });
    }

    public function days($days, $user): self
    {
        return $this->state(function (array $attributes) use ($days, $user) {
            // subtract 1 day, as we include start and end date, so e.g if days is 1, it'll add 1 day and the leave
            // will be created for 2 days
            $days -= 1;

            $starts_on = Carbon::parse($attributes['starts_on']);
            $offDays = $user->getEmployment($starts_on->isFuture() ? now() : $starts_on, true)->off_days;

            // make sure the leave starts_on is not an offday
            while ( ! $starts_on->isWorkingDay($offDays)) {
                $starts_on->addDay();
            }

            return [
                'starts_on' => $starts_on,
                'ends_on' => $starts_on->copy()->addDaysWhere($days, fn($date) => $date->isWorkingDay($offDays)),
            ];
        });
    }

    public function ongoing(): self
    {
        return $this->state(fn($attrs) => [
            'starts_on' => now()->subDay(),
            'ends_on' => now()->addDays(5),
        ]);
    }
}
