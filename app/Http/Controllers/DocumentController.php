<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class DocumentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('log.activity');
    }

    public function index(?Ticket $ticket)
    {
        $this->authorize('viewAny', Document::class);

        $data = [];

        $data['month'] = Cache::get('loadChartMonth', '[]');
        $data['week'] = Cache::get('loadChartWeek', '[]');
        $data['day'] = Cache::get('loadChartDay', '[]');

        $data['ticket'] = $ticket;

        return view('document.index', $data);
    }

    public function show()
    {
        //
    }

    public function edit(Document $document)
    {
        //
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=". $document->name());
        header("Content-Type: application/pdf");

        return readfile($document->getUrl());
    }

    public function destroy($documentId)
    {
        $document = Document::withTrashed()->findOrFail($documentId);

        if ($document->trashed()) {
            $document->forceDelete();
        } else {
            $document->delete();
        }

        return redirect()->back()->with('success', __('Document deleted successfully'));
    }
}
