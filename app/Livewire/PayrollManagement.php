<?php

namespace App\Livewire;

use App\Enums\PayrollStatus;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class PayrollManagement extends Component
{
    use InteractsWithConfirmationModal;

    private PayrollService $payrollService;
    public User $user;
    public Payroll $payroll;

    public array $overtimes = [];
    public array $surcharges = [];

    public function boot(PayrollService $payrollService): void
    {
        $this->payrollService = $payrollService;
    }

    public function rules(): array
    {
        return [
            'payroll.notes' => 'nullable',

            'overtimes.*.hourly_rate' => 'required',
            'overtimes.*.hours' => 'required',

            'surcharges.*.description' => 'required',
            'surcharges.*.amount' => 'required',
            'surcharges.*.tax' => 'required',

            'payroll.leaves_balance' => 'required',
            'payroll.information' => 'nullable',
        ];
    }

    public function mount(Payroll $payroll): void
    {
        $this->payroll = $payroll;
        $this->payroll->load('user');

        $this->user = $payroll->user;

        $this->overtimes = $this->payroll->overtimes->toArray();
        $this->surcharges = $this->payroll->surcharges->toArray();
    }

    public function update(): void
    {
        $this->validate();
    }

    public function render(): View
    {
        $data = array();

        $data['missing_times'] = $this->payrollService->getMissingTimes($this->payroll);
        $data['forgotten_logouts'] = $this->payrollService->getForgottenLogouts($this->payroll);
        $data['abnormalAttendances'] = $this->payrollService->getAbnormalAttendance($this->payroll);
        $data['updated_attendances'] = $this->payrollService->getUpdatedAttendances($this->payroll);
        $data['updated_leaves'] = $this->payrollService->getUpdatedLeaves($this->payroll);
        $data['leavesByReason'] = $this->payroll->leaves;
        $data['vacation'] = $this->payrollService->getVacationDetails($this->payroll);

        return view('livewire.payroll-management', $data);
    }

    public function addSurcharge(): void
    {
        $this->surcharges[] = [
            'amount' => 0,
            'tax' => 'gross',
            'description' => ''
        ];
    }

    public function addOvertime(): void
    {
        $this->overtimes[] = [
            'hours' => 0,
            'hourly_rate' => 0,
        ];
    }

    public function removeSurcharge($index): void
    {
        unset($this->surcharges[$index]);
    }

    public function removeOvertime($index): void
    {
        unset($this->overtimes[$index]);
    }

    public function submit(): void
    {
        $this->validate();

        $this->payroll->overtimes = $this->overtimes;
        $this->payroll->surcharges = $this->surcharges;

        $this->payroll->status = PayrollStatus::IN_PROGRESS;

        $this->payroll->save();

        $this->dispatch('flashNotification', message: __('Payroll info saved'));
    }

    public function process(): void
    {
        $this->askForConfirmation(
            callback: function (){
                try {
                    $this->payroll->process();
                } catch (Exception $exception){
                    $this->dispatch('flashNotification', message: $exception->getMessage(), type: 'error');
                    return;
                }

                $this->dispatch('flashNotification', message: __('Payroll process completed'));
            }
        );
    }
}
