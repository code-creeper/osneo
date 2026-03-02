<?php

namespace App\Livewire\Modals;

use App\Contracts\Modifiable;
use App\Enums\ModificationType;
use App\Livewire\Traits\LogsActivity;
use App\Models\Modification;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ModificationDetails extends Modal
{
    use LogsActivity;

    public Modification|int $modification;
    public ?Modifiable $modifiable;
    public array $changes;

    public function mount(Modification $modification): void
    {
        $this->modification = $modification;
        $this->modifiable = $this->modification->modifiable;

        if ($this->modification->type == ModificationType::Create) {
            $this->modifiable = $this->modification->getNewModifiable();
        }

        $this->changes = $this->modifiable->getFormattedChanges($this->modification);
    }

    public function render(): View
    {
        return view('livewire.modals.modification-details');
    }
}
