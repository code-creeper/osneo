<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class Select2SourceController extends Controller
{
    public function __invoke($source = null)
    {
        $search = request('q');
        $page = request('page');

        $data = [];

        $query = null;
        $key = 'id';
        $value = 'name';

        switch ($source){
            case 'users':
                $query = User::query()
                    ->when($search, fn(Builder $query) => $query->whereLike('first_name', $search));
                break;
            case 'tickets':
                $value = 'number';
                $query = Ticket::query()
                    ->when($search, fn(Builder $query) => $query->whereLike('number', $search));
                break;
        }

        $query = $query->simplePaginate(30, page: $page);

        $data = $query->getCollection()->toKeyValuePair($key, $value);

        // format as per select2 requirements
        $results = array_values(Arr::map($data, function ($label, $key){
            return [
                'id' => $key,
                'text' => $label,
            ];
        }));

        return response()->json([
            'results' => $results,
            "pagination" => [
                'more' => $query->hasMorePages()
            ]
        ]);
    }
}
