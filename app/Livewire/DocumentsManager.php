<?php

namespace App\Livewire;

use App\Models\Document;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use DB;
use Illuminate\Contracts\View\View;
use LexofficeApi;
use Livewire\Component;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Storage;
use App\Livewire\Traits\InteractsWithConfirmationModal;

class DocumentsManager extends Component
{
    use InteractsWithConfirmationModal;

    public $documentTypeId = null;
    public $source = null;
    public $ticketId = null;
    public $month = null;
    public $year = null;
    public $folder = null;
    public $filters = [];
    public $showInbox = true;
    public $selfAssigned = false;
    public $documentProperties = [];

    public array $documentTypesArray = [];

    public $perPageOptions = ['5', '10', '15', '25'];
    public $perPage = 10;

    protected $listeners = [
        'treeChanged' => 'selectFolder',
        'refresh' => '$refresh'
    ];

    public function mount(): void
    {
        $this->documentTypesArray = DocumentType::all()->toArray();
        $this->folder = $this->ticketId ? Document::$sortedDir : Document::$inboxDir;

        if (user()->cannot('sort documents')){
            $this->folder = Document::$sortedDir;
        }
    }

    public function render(): View
    {
        $data = array();

        $inboxFiles = [];

        $this->showInbox = $this->folder == Document::$inboxDir;

        if ($this->showInbox){
            $inboxFiles = $this->prepareInboxFiles();
        }

        //$data['tickets'] = user()->can('view all documents') ? Ticket::all()->toKeyValuePair(value: 'number') : [];

        $allowedDocumentTypes = auth()->user()->getPreference('allowed_document_types');

        $data['documents'] = Document::query()
            ->with('users')
            ->when($this->documentTypeId, fn($query) => $query->where('document_type_id', $this->documentTypeId))
            ->when($this->source, fn($query) => $query->where('source', $this->source))
            ->when($this->year, fn($query) => $query->whereYear('sorted_on', $this->year))
            ->when($this->month, fn($query) => $query->whereMonth('sorted_on', $this->month))
            ->when($this->ticketId, fn($query) => $query->where('ticket_id', $this->ticketId))
            ->when(
                $this->selfAssigned,
                fn($query) => $query->whereIn('id', DB::table('document_user')
                    ->where('user_id', auth()->id())
                    ->pluck('document_id')
                    ->toArray()
                )
            )
            ->when(
                $this->folder == Document::$inboxDir,
                fn($query) => $query->inbox($inboxFiles),
            )
            ->when(
                ! $this->folder or $this->folder == Document::$sortedDir,
                fn($query) => $query->sorted()->allowed($allowedDocumentTypes),
            )
            ->when(
                $this->folder == Document::$trashDir,
                fn($query) => $query->onlyTrashed()
            )
            ->when(! $this->ticketId && $this->folder !== Document::$inboxDir, fn($query) => $query->relevant())
            ->when(
                ! $this->ticketId
                && $this->folder !== Document::$inboxDir
                && ! user()->canAny(['view all documents', 'view own documents', 'view assigned documents']),

                fn($query) => $query->whereRaw('false')
            )
            ->latest($this->folder == Document::$sortedDir ? 'sorted_on' : 'id')
            ->paginate($this->perPage);

        $folders = [];
        $files = DB::table('documents')
            ->addSelect('source',
                DB::raw('(select name from document_types where id = document_type_id) as doc_type'),
                DB::raw('count(id) as `files_count`'),
                DB::raw("YEAR(sorted_on) year, DATE_FORMAT(sorted_on, '%m') month")
            )
            ->where('status', 2)
            ->when(
                user()->cannot('view all documents'),
                fn($query) => $query->whereIn('document_type_id', $allowedDocumentTypes)
            )
            ->groupBy('source', 'doc_type', 'year', 'month')
            ->get();

        $folders[Document::$inboxDir] = [];
        $folders[Document::$sortedDir] = [];
        $folders[Document::$trashDir] = [];

        foreach ($files as $file){
            $folders[Document::$sortedDir][$file->source][$file->doc_type][$file->year][$file->month] = $file->files_count;
        }

        if (user()->cannot('sort documents')){
            unset($folders[Document::$inboxDir]);
        }

        if (user()->cannot('viewAnySorted', Document::class)){
            unset($folders[Document::$sortedDir]);
        }

        $data['folders'] = $folders;
        $this->dispatch('updateTree');

        $data['menu'] = $this->buildMenu();

        return view('livewire.documents-manager', $data);
    }

    public function selectFolder($filters): void
    {
        $this->filters = $filters;
        $docTypes = collect($this->documentTypesArray)->keyBy('name')->pluck('id', 'name')->toArray();
        $this->folder = $filters['folder'];
        $this->source = $filters['source'] ?? null;
        $this->documentTypeId = $docTypes[$filters['doc_type'] ?? null] ?? null;
        $this->year = $filters['year'] ?? null;
        $this->month = $filters['month'] ?? null;

        if($this->documentTypeId){
            $this->documentProperties = DocumentProperty::where('document_type_id', $this->documentTypeId)->get();
        } else {
            $this->documentProperties = [];
        }

        if ($this->folder == Document::$inboxDir){
            $this->ticketId = null;
            $this->selfAssigned = false;
        }
    }

    // Check all inbox files and add in database if not added
    public function prepareInboxFiles(): array
    {
        $inboxFiles = Document::getInboxFiles();

        $newFilesExist = Document::whereIn('status', [0, 1])
            ->whereIn('name', $inboxFiles)->count() != count($inboxFiles);

        if (!$newFilesExist){
            return $inboxFiles;
        }

        foreach ($inboxFiles as $fileName) {
            Document::whereIn('status', [0, 1])->firstOrCreate([
                'name' => $fileName, 'status' => 0,
            ]);
        }

        return $inboxFiles;
    }

    public function resetFilters(): void
    {
        $this->reset(['ticketId', 'selfAssigned']);
    }

    public function restore($documentId): void
    {
        $document = Document::onlyTrashed()->findOrFail($documentId);

        $this->authorize('restore', $document);

        $document->restore();

         $this->dispatch('flashNotification', message: __('Document restored successfully'));
    }

    public function sendToLexOffice(Document $document): void
    {
        $tempPath = "temp/$document->name";

        try {
            Storage::disk('local')->put($tempPath, Storage::disk('s3')->get($document->pdf_path));
            LexofficeApi::upload_file(storage_path("app/$tempPath"));

             $this->dispatch('flashNotification', message: __('Document has been sent to Lexoffice'));
        } catch (\Exception | \TypeError $exception){
            $this->dispatch('flashNotification', $exception->getMessage(), type: 'error');
        }

        Storage::disk('local')->delete($tempPath);
        $document->forceDelete();
    }

    public function delete($documentId): void
    {
        $document = Document::withTrashed()->findOrFail($documentId);

        $this->authorize('delete', $document);

        $this->askForConfirmation(function () use ($document){
            if ($document->trashed()) {
                $document->forceDelete();
            } else {
                $document->delete();
            }

            $this->dispatch('flashNotification', message: __("Document Deleted Successfully"));
        });
    }

    public function buildMenu(): MenuColumn
    {
        return MenuColumn::make('Actions')
            ->actions([
                MenuItem::make('')
                    ->title(fn($row) => 'Assign Users')
                    ->icon('fal fa-user-tag mr-1')
                    ->permission(fn($row) => user()->can('assign', $row))
                    ->wireElement(fn($row) => [
                        'type' => 'modal',
                        'component' => 'modals.assign-document-users',
                        'params' => ['document' => $row->id]
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => 'View')
                    ->icon('fal fa-eye mr-1')
                    ->permission(fn($row) => user()->can('view', $row))
                    ->wireElement(fn($row) => [
                        'type' => 'modal',
                        'component' => 'modals.document-viewer',
                        'params' => ['document' => $row->id]
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => 'Download')
                    ->icon('fal fa-download me-1')
                    ->permission(fn($row) => user()->can('view', $row))
                    ->location(fn($row) => route('documents.download', $row))
                    ->attributes(fn($row) => [
                        'target' => '_blank',
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => 'Send to Lexoffice')
                    ->icon('fal fa-upload me-1')
                    ->permission(fn($row) => user()->can('update', $row))
                    ->visible(fn($row) => !$row->sorted && !$row->isLexOfficeFile())
                    ->attributes(fn($row) => [
                        'wire:click' => "sendToLexOffice($row->id)"
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => $row->sorted ? 'Edit' : 'Sort')
                    ->icon('fal fa-pen me-1')
                    ->permission(fn($row) => user()->can('update', $row))
                    ->wireElement(fn($row) => [
                        'type' => 'modal',
                        'component' => 'modals.document-processor',
                        'params' => ['document' => $row->id,]
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => 'Delete')
                    ->icon('fa fa-trash me-1')
                    ->permission(fn($row) => user()->can('delete', $row))
                    ->attributes(fn($row) => [
                        'class' => 'text-danger',
                        'wire:click' => "delete($row->id)",
                    ]),

                MenuItem::make('')
                    ->title(fn($row) => 'Restore')
                    ->icon('fal fa-trash-undo me-1')
                    ->permission(fn($row) => user()->can('restore', $row))
                    ->attributes(fn($row) => [
                        'wire:click' => "restore($row->id)",
                    ]),
            ]);
    }
}
