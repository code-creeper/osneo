<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Announcement;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class AnnouncementDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Announcement::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Announcements");

        $this->emptyHeader();
    }

    public function builder(): Builder
    {
        return Announcement::query()
            ->select('announcements.*')
            ->with(['recipients'])
            ->withCount(['recipients', 'readRecipients']);
    }

    public function columns(): array
    {
        return [
            Column::make("Subject")->searchable(),
            Column::make("Audience")->from(fn($row) => $this->getAudience($row))->html(),
            LinkColumn::make("Read By")
                ->title(fn($row) => "$row->read_recipients_count/$row->recipients_count")
                ->location(fn($row) => '#')
                ->wireElement(fn($row) => [
                    'type' => 'slide-over',
                    'component' => 'modals.announcement-recipients-modal',
                    'params' => ['announcement' => $row->id]
                ]),
            Column::make("Announced On", 'created_at')
                ->format(fn($value) => $value->date())
                ->sortable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Preview')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-eye me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.announcement-modal',
                            'params' => ['announcement' => $row->id]
                        ]),
                    MenuItem::make('')
                        ->title(fn($row) => 'Audience')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-users me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'slide-over',
                            'component' => 'modals.announcement-recipients-modal',
                            'params' => ['announcement' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.announcement-form',
                            'params' => ['announcement' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fa fa-trash me-1')
                        ->permission(fn($row) => user()->can('delete', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)"
                        ])
                ]),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Announcement::class))
                ->wireModal('forms.announcement-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    #[On('delete-announcement')]
    public function delete(Announcement $announcement): void
    {
        $this->authorize('delete', $announcement);

        $this->askForConfirmation(function () use ($announcement){
            $announcement->users()->detach();
            $announcement->delete();

            $this->dispatch('modal.close', force: true);

            $this->dispatch('flashNotification', message: __("Announcement deleted"));
        });
    }

    public function getAudience(Announcement $announcement): string
    {
        if ($announcement->audience == 'all') {
            return "All Users";
        }

        if ($announcement->audience == 'role') {
            $roles = Role::find($announcement->role_ids);

            $value = "";

            foreach ($roles as $role) {
                $value .= "<span class='badge badge-outline-success py-1 badge-normal me-1'>$role->display_name</span>";
            }

            return $value;
        }

        if ($announcement->audience == 'user' && $announcement->recipients_count <= 5) {
            $value = "";

            foreach ($announcement->recipients as $user) {
                $value .= "<span class='badge badge-outline-info py-1 badge-normal me-1'>$user->name</span>";
            }

            return $value;
        }

        return 'Specific Users';
    }
}
