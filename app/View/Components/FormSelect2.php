<?php

namespace App\View\Components;

use Illuminate\Support\Arr;
use ProtoneMedia\LaravelFormComponents\Components\FormSelect;

class FormSelect2 extends FormSelect
{
    public array $config = [];
    public ?string $source;
    public array $selectedOptions = [];

    private array $sources = [];

    public function __construct(
        string $name,
        string $label = '',
        array $options = [],
        ?bool $bind = null,
        mixed $default = null,
        bool $multiple = false,
        bool $showErrors = true,
        bool $manyRelation = false,
        bool $floating = false,
        string $placeholder = '',
        string $wrapperClass = '',
        bool $placeholderDisabled = false,
        array $config = [],
        string $source = null,
        array $selectedOptions = []
    ) {

        parent::__construct(
            $name,
            $label,
            $options,
            $bind,
            $default,
            $multiple,
            $showErrors,
            $manyRelation,
            $floating,
            $placeholder,
            $wrapperClass,
            $placeholderDisabled
        );

        $this->source = $source;
        $this->sources = $this->configureSources();
        $this->selectedOptions = $selectedOptions;

        $options = array_values(Arr::map($options, function ($label, $key){
            return [
                'id' => $key,
                'text' => $label,
            ];
        }));

        $this->selectedOptions = array_values(Arr::map($selectedOptions, function ($label, $key){
            return [
                'id' => $key,
                'text' => $label,
            ];
        }));


        $defaultConfig = [
            //'dropdownParent' => 'body',
            'placeholder' => $placeholder,
            'multiple' => $multiple,
            'data' => $options,
            'selectedOptions' => $this->selectedOptions,
        ];

        $this->config = array_merge($defaultConfig, $config);

        if ($source && isset($this->sources[$source])) {
            $ajaxConfig = $this->sources[$source];
            $ajaxConfig['delay'] = 250;

            $this->config['ajax'] = $ajaxConfig;

            unset($this->config['data']);
        }
    }

    private function configureSources(): array
    {
        return array(
            'users' => [
                'url' => route('select2-sources', ['source' => 'users']),
            ],
            'tickets' => [
                'url' => route('select2-sources', ['source' => 'tickets']),
            ],
        );
    }
}
