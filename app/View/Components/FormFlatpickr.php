<?php

namespace App\View\Components;

use ProtoneMedia\LaravelFormComponents\Components\FormInput;

class FormFlatpickr extends FormInput
{
    public array $config = [];
    public ?string $preset;

    private array $presets = [
        'timepicker' => [
            'enableTime' => true,
            'noCalendar' => true,
            'dateFormat' => 'H:i'
        ],
        'dateRangePicker' => [
            'mode' => 'range',
        ],
    ];

    public function __construct(
        string $name,
        string $label = '',
        string $type = 'text',
        ?bool $bind = null,
        mixed $default = null,
        ?string $language = null,
        bool $showErrors = true,
        bool $floating = false,
        string $wrapperClass = '',
        array $config = [],
        string $preset = null,
    ) {

        parent::__construct($name, $label, $type, $bind, $default, $language, $showErrors, $floating, $wrapperClass);

        $this->preset = $preset;

        $defaultConfig = [
            'dateFormat' => config('dates.defaults.date'),
            'locale' => session()->get('locale', app()->getLocale()),
            'minDate' => null,
            'maxDate' => null,
            'defaultValue' => $default,
        ];

        $presetConfig = $this->presets[$preset] ?? [];

        $this->config = array_merge($defaultConfig, $presetConfig, $config);
    }
}
