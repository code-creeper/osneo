<?php

namespace App\Livewire\Modals;

use App\Enums\InsuranceClaimStatus;
use App\Helpers\LeavesHelper;
use App\Livewire\Traits\LogsActivity;
use App\MediaLibrary\Media;
use App\Models\Document;
use App\Models\InsuranceClaim;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia;
use WireElements\Pro\Components\Modal\Modal;

class ProcessInsuranceClaim extends Modal
{
    use LogsActivity;
    use WithMedia;

    public array $documents = [];
    public Leave|int $leave;
    public InsuranceClaim $claim;

    public ?string $response = null;

    public string $starts_on;
    public string $ends_on;

    public string $title;

    public function mount(Leave $leave): void
    {
        $this->title = __('Claim Insurance Process');

        $this->leave = $leave;

        $this->starts_on = $leave->starts_on->date();
        $this->ends_on = $leave->ends_on->date();

        $this->claim = InsuranceClaim::firstOrCreate(
            [
                'user_id' => $leave->user_id,
                'leave_id' => $leave->id,
            ],
            ['status' => InsuranceClaimStatus::OPEN]
        );
    }

    public function render(): View
    {
        $data = array();

        $data['datePickerConfig'] = [
            'minDate' => $this->starts_on,
            'maxDate' => $this->ends_on
        ];

        return view('livewire.modals.process-insurance-claim', $data);
    }

    public function submit(): void
    {
        // if claim is unconfirmed, we have to wait 2 weeks before applying again
        if ($this->claim->isUnconfirmed()){
            return;
        }

        // if the claim is confirmed previously, we just confirm billing and close claim
        if ($this->claim->isConfirmed()){
            $this->claim->update([
                'status' => InsuranceClaimStatus::DONE
            ]);

            $this->close(andDispatch: [
                'refresh',
                'flashNotification' => [__('Claim processed successfully')],
            ]);

            return;
        }

        $this->uploadDocument();

        if ($this->claim->isOpen()) {
            $this->initiateClaimRequest();
        } else {
            $this->recordClaimResponse();
        }

        $this->close(andDispatch: [
            'refresh'
        ]);
    }

    public function initiateClaimRequest(): void
    {
        $this->claim->update([
            'status' => InsuranceClaimStatus::WAITING
        ]);
    }

    public function recordClaimResponse(): void
    {
        $attempt = $this->claim->attempt + 1;

        $status = InsuranceClaimStatus::CONFIRMED;

        if ($this->response == 'rejected'){
            $status = $attempt > 1 ? InsuranceClaimStatus::REJECTED : InsuranceClaimStatus::UNCONFIRMED;
        }

        $this->claim->update([
            'attempt' => $attempt,
            'status' => $status,
            'last_requested_on' => today()
        ]);

        if($this->claim->isConfirmed() && $this->claimApprovedPartially()){
            $this->splitLeave();
        }

        if ($this->claim->isRejected()){
            $this->leave->update([
               'reason_id' => config('app.rejected_sick_leave_reason_id')
            ]);
        }
    }

    public function claimApprovedPartially(): bool
    {
        $this->starts_on = Carbon::parse($this->starts_on);
        $this->ends_on = Carbon::parse($this->ends_on);

        $approvedDays = LeavesHelper::getLeaveDates($this->starts_on, $this->ends_on, $this->leave->user)->count();

        return $approvedDays != $this->leave->days;
    }

    public function splitLeave(): void
    {
        $leave = $this->leave;
        $user = $this->leave->user;

        // check if the start date of approved days is same as start_date of leave
        // and create new leave for unapproved days before the start date of leave
        $newLeaveStartsOn = $this->starts_on->clone()->subDay();
        $days = LeavesHelper::getLeaveDates($leave->starts_on, $newLeaveStartsOn, $user)->count();
        if ($this->starts_on != $leave->starts_on && $days != 0){
            $newLeave = $leave->replicate();

            $newLeave->starts_on = $leave->starts_on;
            $newLeave->ends_on = $newLeaveStartsOn;
            $newLeave->days = $days;

            $newLeave->save();
        }

        // check if the end date of approved days is same as end_date of leave
        // and create new leave for unapproved days before the end date of leave
        $newLeaveEndsOn = $this->ends_on->clone()->addDay();
        $days = LeavesHelper::getLeaveDates($newLeaveEndsOn, $leave->ends_on, $user)->count();
        if ($this->ends_on != $leave->ends_on && $days != 0){
            $newLeave = $leave->replicate();

            $newLeave->starts_on = $newLeaveEndsOn;
            $newLeave->ends_on = $leave->ends_on;
            $newLeave->days = $days;

            $newLeave->save();
        }

        $leave->update([
            'starts_on' => $this->starts_on,
            'ends_on' => $this->ends_on,
            'days' => LeavesHelper::getLeaveDates($this->starts_on, $this->ends_on, $user)->count()
        ]);

    }

    public function uploadDocument(): void
    {
        $this->validate([
            'documents' => 'required',
        ]);

        foreach ($this->documents as $uuid => $document) {
            $file = Media::where('uuid', $uuid)->first();

            $documentName = (new Document())->makeNameUnique($file->name);

            $pdf = \Storage::get($file->getPath());

            Storage::disk('s3')->put("Inbox/$documentName", $pdf, [
                'visibility' => 'private',
                'ContentType' => $file->mime_type,
                'ContentDisposition' => 'inline',
            ]);

            Document::create([
                'documentable_type' => InsuranceClaim::class,
                'documentable_id' => $this->claim->id,
                'name' => $documentName,
                'uploaded_by' => user()->id
            ]);
        }

        $this->dispatch('refresh');
        $this->dispatch('flashNotification', message: __('Documents are uploaded'));

        $this->documents = [];
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl',
        ];
    }
}
