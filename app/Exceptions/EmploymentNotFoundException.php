<?php

namespace App\Exceptions;

use Exception;

class EmploymentNotFoundException extends Exception
{
    protected $message = "No employment found for this date";
}
