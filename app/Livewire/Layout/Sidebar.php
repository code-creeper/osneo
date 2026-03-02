<?php

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Sidebar extends Component
{
    protected $listeners = ['refreshPage' => '$refresh'];

    public function render(): View
    {
        return view('livewire.layout.sidebar');
    }
}
