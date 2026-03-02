<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class AssignDocumentUsers extends Modal
{
    use LogsActivity;

    public Document|int $document;

    public array $selectedUsers = [];

    public function mount(Document $document): void
    {
        $this->document = $document;
        $this->selectedUsers = $document->users->pluck('id')->toArray();
    }

    public function render(): View
    {
        $data = array();

        $data['title'] = __('Assign Users');
        $data['users'] = User::relevant()->get()->toKeyValuePair();

        return view('livewire.modals.assign-document-users', $data);
    }

    public function submit(): void
    {
        $this->validate([
            'selectedUsers' => 'required'
        ]);

        $this->document->users()->sync($this->selectedUsers);

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Users assigned to document')]
        ]);
    }
}
