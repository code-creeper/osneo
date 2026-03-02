<?php

namespace App\Inserts;

use App\Models\Contact;
use WireElements\Pro\Components\Insert\InsertQueryResult;
use WireElements\Pro\Components\Insert\InsertQueryResults;
use WireElements\Pro\Components\Insert\Types\InsertType;

class ContactInsert extends InsertType
{
    protected string $delimiter = '';
    protected string $match = '\w{1,20}$';

    public function search($query, $scope = []): InsertQueryResults
    {
        $insert = $scope['insert'] ?? 'name';

        return InsertQueryResults::make(
            Contact::query()
                ->where('name', 'like', "%{$query}%")
                ->where('is_customer', 1)
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(function ($contact) use($insert) {
                    return InsertQueryResult::make(
                        id: $contact->id,
                        headline: "$contact->name - {$contact->customer->number}",
                        insert: $contact->{$insert},
                    );
                }));
    }
}
