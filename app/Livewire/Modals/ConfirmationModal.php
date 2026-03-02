<?php

namespace App\Livewire\Modals;

use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\ConfirmationModal as BaseConfirmationModal;

class ConfirmationModal extends BaseConfirmationModal
{
    public string|null $comments = null;
    public bool $confirmWithComments = false;

    public mixed $commentRules = 'nullable';

    public function mount($callbackComponent, array $prompt = [], array $tableHeaders = [], array $tableData = [], $confirmPhrase = null, $theme = 'warning', $metaData = [], $modalCloseArguments = [], $confirmWithComments = false, $commentRules = 'nullable'): void
    {
        parent::mount($callbackComponent, $prompt, $tableHeaders, $tableData, $confirmPhrase, $theme, $metaData, $modalCloseArguments);

        $this->confirmWithComments = $confirmWithComments;
        $this->commentRules = $commentRules;
    }

    public function confirm(): void
    {
        $this->validate();

        $this->dispatch('actionConfirmed', comments: $this->comments)->to($this->callbackComponent);

        call_user_func_array([$this, 'close'], $this->modalCloseArguments);
    }

    public function getMessages(): array
    {
        return [
            'confirmPhraseInput.in' => __('wire-elements-pro::modal.confirmation.please_enter_phrase_to_continue', ['phrase' => $this->confirmPhrase]),
        ];
    }

    public function getRules(): array
    {
        return [
            'confirmPhraseInput' => ['required_with:confirmPhrase', 'in:'.$this->confirmPhrase],
            'comments' => $this->commentRules
        ];
    }

    public function render(): View
    {
        return view('livewire.modals.confirmation-modal');
    }
}
