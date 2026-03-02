<?php

namespace App\Inserts;

use App\Models\Ticket;
use WireElements\Pro\Components\Insert\InsertQueryResult;
use WireElements\Pro\Components\Insert\InsertQueryResults;
use WireElements\Pro\Components\Insert\Types\InsertType;

class TicketInsert extends InsertType
{
    protected string $delimiter = '';
    protected string $match = '\w{1,20}$';

    public function search($query): InsertQueryResults
    {
        return InsertQueryResults::make(
            Ticket::query()
                ->where('number', 'like', "%{$query}%")
                ->orderBy('number')
                ->limit(10)
                ->get()
                ->map(function ($ticket) {
                    return InsertQueryResult::make(
                        id: $ticket->id,
                        headline: $ticket->number,
                        insert: $ticket->number,
                    );
                }));
    }
}
