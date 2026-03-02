<?php

namespace App\Livewire\Forms;

use App\Enums\LeaveAction;
use App\Helpers\LeavesHelper;
use App\Livewire\Traits\InteractsWithConfirmationModal;
use App\Livewire\Traits\LogsActivity;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\LeaveActionTaken;
use App\Validation\ValidateLeaveDates;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Validator;
use WireElements\Pro\Components\Modal\Modal;

class LeaveForm extends Modal
{
    use LogsActivity;

    public Leave|int $leave;

    public bool $editing = false;
    public string $title;

    public ?int $userId = null;
    public string|array $hiddenFields = [];

    private CarbonPeriod $leaveDates;
    public ?string $dates = null;
    public array $selectedTags = [];

    public ?string $comments = null;

    public function boot(): void
    {
        $this->withValidator(function (Validator $validator) {
            $validator->after([
                new ValidateLeaveDates(User::find($this->leave->user_id), $this->leave, [
                    'starts_on' => $this->leave->starts_on,
                    'ends_on' => $this->leave->ends_on,
                    'reason_id' => $this->leave->reason_id,
                ]),
                function (Validator $validator) {
                    if ($validator->errors()->isNotEmpty()){
                        return;
                    }

                    try {
                        $this->leaveDates = LeavesHelper::getLeaveDates(
                            $this->leave->starts_on,
                            $this->leave->ends_on,
                            User::find($this->leave->user_id)
                        );
                    } catch (Exception $exception){
                        $validator->errors()->add('starts_on', $exception->getMessage());
                    }
                },
            ]);
        });
    }

    public function mount(Leave $leave): void
    {
        if ( ! is_array($this->hiddenFields)) {
            $this->hiddenFields = array($this->hiddenFields);
        }

        $this->title = __('Add New Leave');
        $this->leave = $leave;

        if ($this->leave->id) {
            $this->editing = true;
            $this->title = __('Edit Leave');

            $this->dates = $this->leave->starts_on->date() . " to ". $this->leave->ends_on->date();

            $this->selectedTags = $this->leave->tags->pluck('id')->toArray();

        } else {
            $this->leave->user_id = null;
        }

        if ( ! $this->editing && auth()->user()->cannot('create leaves for all')) {
            $this->leave->user_id = auth()->id();
            $this->hiddenFields[] = 'user_id';
        }

        if ($this->userId) {
            $this->leave->user_id = $this->userId;
        }
    }

    public function render(): View
    {
        $data = array();
        $data['users'] = User::relevant()->get()->toKeyValuePair();
        $data['tags'] = Tag::where('model', 'App\Models\Leave')->get()->toKeyValuePair();
        $data['reasons'] = LeaveReason::all()->toKeyValuePair();

        return view('livewire.forms.leave-form', $data);
    }

    public function rules(): array
    {
        return [
            'leave.user_id' => [$this->editing ? 'nullable' : 'required', 'exists:users,id'],
            'leave.created_by' => ['nullable', 'exists:users,id'],
            'leave.reason_id' => ['bail', 'required', 'integer', 'exists:leave_reasons,id'],
            'dates' => ['required'],
            'leave.starts_on' => ['required', 'date'],
            'leave.ends_on' => ['required', 'date'],
            'leave.days' => ['nullable'],
            'selectedTags' => ['nullable'],
        ];
    }

    public function submit(): void
    {
        $this->leave->starts_on = Carbon::parse(str($this->dates)->before('to'));
        $this->leave->ends_on = Carbon::parse(str($this->dates)->after('to'));

        if ( ! $this->editing) {
            $this->authorize('create', $this->leave);
            $this->create();
        } else {
            $this->authorize('update', $this->leave);
            $this->update();
        }
    }

    private function create(): void
    {
        $this->validate();

        $this->leave->created_by = $this->leave->user_id == auth()->id() ? null : auth()->id();
        $this->leave->days = $this->leaveDates->count();

        if ($this->leave->created_by) {
            $this->authorize('create leaves for all');
        }

        DB::beginTransaction();

        try {
            $this->leave->save();

            if (user()->can('tag leaves')) {
                $this->leave->tags()->sync($this->selectedTags);
            }

            // pre-approve a leave, if user have permission for it
            if (user()->can('preApprove', $this->leave)) {
                $this->leave->approve();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $this->dispatch('flashNotification', message: $exception->getMessage());

            return;
        }

        DB::commit();

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Leave requested successfully')],
            'refresh'
        ]);

    }

    private function update(): void
    {
        $this->validate();

        $this->leave->days = $this->leaveDates->count();
        $editingLeaveForOtherUser = user()->id !== $this->leave->user_id;

        if ($editingLeaveForOtherUser) {
            $this->authorize('edit any leaves');
        }

        if (user()->can('edit leaves without approval')) {

            $this->leave->save();

            if (user()->can('tag leaves')) {
                $this->leave->tags()->sync($this->selectedTags);
            }

            // if leave is updated by someone else, send notification to user
            if ($editingLeaveForOtherUser) {
                $this->leave->user->notify(new LeaveActionTaken($this->leave, LeaveAction::Updated));
            }

            $this->close(andDispatch: [
                'flashNotification' => ['message' => __('Leave updated successfully')],
                'refresh'
            ]);

            return;
        }

        $this->sendModificationRequest();
    }

    private function sendModificationRequest(): void
    {
        $data = $this->leave->toArray();
        $data['comments'] = $this->comments;

        if ($this->leave->createModification($data)) {
            $this->close(andDispatch: [
                'flashNotification' => ['message' => __('Request for modification has been sent to admin')],
                'refresh',
            ]);

            return;
        }

        $this->dispatch('flashNotification', message: __('No changes were made'), type: 'info');
    }
}
