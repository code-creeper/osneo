<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\On;
use ReflectionMethod;

trait InteractsWithConfirmationModal
{
    use \WireElements\Pro\Concerns\InteractsWithConfirmationModal;

    public ?string $confirmationComments = null;

    public function askForConfirmation(
        callable $callback,
        array $prompt = [],
        $tableHeaders = [],
        $tableData = [],
        $confirmPhrase = null,
        $theme = 'warning',
        $metaData = [],
        $modalBehavior = [],
        $modalAttributes = [],
        $modalCloseArguments = [],
        $confirmWithComments = false,
        $commentRules = 'nullable',
    ): void {
        if ($this->actionConfirmed) {
            $callback($this->confirmationComments);
            $this->actionConfirmed = false;

            return;
        }

        $trace = debug_backtrace();
        $trace = next($trace);

        $this->confirmationCaller = $trace['function'] ?? null;
        $this->confirmationCallerArguments = $trace['args'] ?? [];

        $this->dispatch('modal.open', 'modals.confirmation-modal', [
            $this->getName(),
            $prompt,
            $tableHeaders,
            $tableData,
            $confirmPhrase,
            $theme,
            $metaData,
            $modalCloseArguments,
            $confirmWithComments,
            $commentRules,
        ], $modalAttributes, $modalBehavior);
    }

    #[On('actionConfirmed')]
    public function actionConfirmed($comments = null): void
    {
        if (method_exists($this, $this->confirmationCaller)) {
            $reflection = new ReflectionMethod($this, $this->confirmationCaller);
            if ($reflection->isPublic()) {
                $this->actionConfirmed = true;
                $this->confirmationComments = $comments;

                $this->{$this->confirmationCaller}(...$this->confirmationCallerArguments);
            }
        }
    }
}
