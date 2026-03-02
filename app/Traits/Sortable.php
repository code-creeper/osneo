<?php

namespace App\Traits;


use Illuminate\Support\Facades\Schema;
use Str;

trait Sortable
{
    public function scopeSort($query, $orderBy = 'id', $order = 'asc', $columns = []){
        $sortIsValid = false;

        if (Schema::hasColumn($this->getTable(), $orderBy)){
            $sortIsValid = true;
        }

        if(in_array($orderBy, $columns)){
            $sortIsValid = true;
        }

        if ($sortIsValid) {
            $query->orderBy($orderBy, $order);
        } else{
            $query->orderBy('id', 'desc');
        }
        return $query;
    }
}
