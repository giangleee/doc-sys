<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ExcelValidation extends Exception
{
    protected $errors;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors = [])
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function render()
    {
        return responseError(422, $this->getMessage(), $this->errors);
    }
}
