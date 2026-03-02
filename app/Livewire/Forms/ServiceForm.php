<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Rules\MaxWithFieldsRule;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ServiceForm extends Modal
{
    use LogsActivity;

    protected $listeners = ['categoryCreated' => 'newCategoryCreated'];

    public Service|int $service;

    public array $categories = [];

    public array $sizes = [];

    public string $heading;
    public bool $editing = false;
    public bool $descriptionCanBeCopied = false;

    public function mount(Service $service): void
    {
        $this->heading = __('Create Service');
        $this->service = $service;

        if ($this->service->id) {
            $this->editing = true;
            $this->heading = __('Edit Service');
            $this->sizes = $service->sizes->toArray();
        } else {
            $this->service->service_category_id = null;
        }
    }

    protected function rules(): array
    {
        return [
            'service.name' => [
                'required',
                new MaxWithFieldsRule('195', 'service.unit'),
            ],
            'service.unit' => [
                'required',
                new MaxWithFieldsRule('195', 'service.name'),
            ],
            'service.description' => 'required|max:2000',
            'service.service_category_id' => 'nullable',
            'sizes' => 'required',
            'sizes.*' => 'required',
            'sizes.*.price' => 'required|decimal:2',
            'sizes.*.name' => [
                'required',
                new MaxWithFieldsRule('195', 'service.name', 'service.unit')
            ],
        ];
    }

    public function updatedService($value, string $key): void
    {
        if ($key == 'service_category_id') {
            $this->descriptionCanBeCopied = true;
        }
    }

    public function newCategoryCreated(int $categoryId): void
    {
        $this->categories = ServiceCategory::all()->toKeyValuePair();
        $this->service->service_category_id = $categoryId;
    }

    public function copyCategoryDescription(): void
    {
        $category = ServiceCategory::find($this->service->service_category_id);
        $this->service->description = $category?->description;
    }

    public function render(): View
    {
        $data = array();

        $this->categories = ServiceCategory::all()->toKeyValuePair();

        return view('livewire.forms.service-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->service->sizes = $this->sizes;
        $this->service->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Service updated')]
        ]);
    }

    public function removeSize(int $index): void
    {
        unset($this->sizes[$index]);
    }

    public function addSize(): void
    {
        $this->sizes[] = [
            'name' => null,
            'price' => 0.00,
        ];
    }

    public static function attributes(): array
    {
        return [
            'size' => '4xl',
        ];
    }
}
