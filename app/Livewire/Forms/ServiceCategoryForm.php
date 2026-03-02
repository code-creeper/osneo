<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ServiceCategoryForm extends Modal
{
    use LogsActivity;

    public ServiceCategory|int $serviceCategory;

    public string $title;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'serviceCategory.name' => 'required|max:50',
            'serviceCategory.description' => 'nullable|max:2000',
        ];
    }

    public function mount(ServiceCategory $serviceCategory): void
    {
        $this->title = __('Create Service Category');
        $this->serviceCategory = $serviceCategory;

        if ($this->serviceCategory->id){
            $this->editing = true;
            $this->title = __('Edit Service Category');
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.service-category-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->serviceCategory->save();

        $this->close(andDispatch: [
            'categoryCreated' => [$this->serviceCategory->id],
            'flashNotification' => ['message' => __('Service category updated')],
        ]);
    }
}
