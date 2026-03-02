<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultilingualFormInput extends Component
{
    public string $name;
    public string $label;
    public string $type;
    public bool $floating;
    public string $wrapperClass;
    public bool $showErrors;

    public string $currentLocale;

    public function __construct(
        string $name,
        string $label = '',
        string $type = 'text',
        bool $showErrors = true,
        bool $floating = false,
        string $wrapperClass = '',
    ) {
        $this->name       = $name;
        $this->label      = $label;
        $this->type       = $type;
        $this->showErrors = $showErrors;
        $this->floating   = $floating;
        $this->wrapperClass   = $wrapperClass;

        $this->currentLocale = config('app.locale');
    }


    public function render(): View
    {
        return view('components.multi-lingual-form-input');
    }
}
