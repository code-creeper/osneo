<?php

namespace App\Livewire\Modals;

use App\Contracts\ActivityFormatter;
use App\Livewire\Traits\LogsActivity;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\SlideOver\SlideOver;

class ChangesHistoryModal extends SlideOver
{
    use LogsActivity;

    public ActivityFormatter $model;

    public function mount($modelType, $modelId): void
    {
        $this->model = $modelType::find($modelId);
    }

    public function render(): View
    {
        $data = array();

        $activities = $this->model
            ->activities()
            ->latest()
            ->get();

        $data['activities'] = $activities;

        return view('livewire.modals.changes-history-modal', $data);
    }

    public static function behavior(): array
    {
        return [
            'close-on-escape' => true,
            'close-on-backdrop-click' => true,
        ];
    }

}
