<?php

namespace App\LaravelLivewireTables\Traits;

use Rappasoft\LaravelLivewireTables\Traits\WithActions;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;

trait WithCustomizations
{
    use WithActions;

    public ?string $title;

    public function useDefaults(): void
    {
        $this->defaultSortColumn = 'id';
        $this->defaultSortDirection = 'desc';

        $this->setPrimaryKey('id');

        $this->setPerPageAccepted([25, 50, 100]);
        $this->setPerPage(25);

        $this->setTableAttributes([
            'default' => true,
            'class' => 'table-sm',
        ]);

        $this->setThAttributes(function (Column $column) {
            if ($column instanceof MenuColumn) {
                return [
                    'style' => 'visibility:hidden',
                ];
            }

            return [];
        });

        $this->setTdAttributes(function (Column $column) {
            if ($column instanceof MenuColumn) {
                return ['class' => 'text-end'];
            }

            return [];
        });

        $this->setFilterLayoutSlideDown();
        $this->setFilterSlideDownDefaultStatusEnabled();
    }

    public function setTitle($title): void
    {
        $this->title = __($title);
    }

    public function prependColumns(): array
    {
        return [
            Column::make('Id')
                ->sortable()
                ->searchable()
                ->hideIf(!user()->isAdmin())
                ->deselected(),
        ];
    }

    public function emptyHeader(): void
    {
        $this->setSearchDisabled();
        $this->setPerPageVisibilityDisabled();
        $this->setColumnSelectDisabled();
    }
}
