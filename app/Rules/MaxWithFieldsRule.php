<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class MaxWithFieldsRule implements ValidationRule
{
    public array $fields;
    public string $max;

    public function __construct(int $max, ...$fields)
    {
        $this->max = $max;
        $this->fields = $fields;
    }

    /*
     * Validate the field under validation has a max length combining the length of all other given fields
     * */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request()->all();
        $data = array();

        if (isset($request['serverMemo'])){
            $data = $request['serverMemo']['data'];
        }

        $length = str($value)->length();

        foreach ($this->fields as $field){
            if(Arr::has($data, $field)){
                $length += str(Arr::get($data, $field))->length();
            }
        }

        $attributeName = implode(' + ', Arr::flatten(array($this->fields, $attribute)));

        if ($length > $this->max){
            $fail("The $attributeName must not be greater than $this->max characters.");
        }
    }

}
