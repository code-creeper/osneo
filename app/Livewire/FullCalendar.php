<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class FullCalendar extends Component
{
    public array $config;
    public array $eventSources;

    public function mount(): void
    {
        $defaultConfig = [
            'schedulerLicenseKey' => '0025011460-fcs-1640720354',
            'initialDate' => now()->toDateString(),
            'locale' => config('app.locale'),
            'initialView' => 'dayGridMonth',
            'height' => '100%',
            'aspectRatio' => 1.5,
            'headerToolbar' => [
                'left' => 'today prev,next',
                'center' => 'title',
                'right' => 'dayGridMonth,dayGridWeek,timeGridDay,listWeek',
            ],
            'editable' => false,
            'eventMaxStack' => 1,
        ];

        $config = $this->configure();

        $this->config = array_merge($defaultConfig, $config);

        $this->eventSources = $this->eventSources();
    }


    public abstract function configure(): array;

    public abstract function eventSources(): array;


    public function render(): View
    {
        return view('livewire.fullcalendar');
    }
}
