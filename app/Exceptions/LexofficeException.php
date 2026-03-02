<?php

namespace App\Exceptions;

use Exception;

class LexofficeException extends Exception
{
    private mixed $custom_error;

    public function __construct(string $message, array $data = [])
    {
        $this->custom_error = $data;
        parent::__construct($message);
    }

    public function get_error(): array
    {
        return $this->custom_error;
    }
}
