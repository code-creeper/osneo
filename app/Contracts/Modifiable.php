<?php


namespace App\Contracts;


use App\Enums\ModificationType;
use App\Models\Modification;

interface Modifiable
{
    public function applyChanges(Modification $modification): void;

    public function applyDeletion(): void;

    public function getFormattedChanges(Modification $modification): array;

    public function createModification(array $changes, ModificationType $type): bool;
}
