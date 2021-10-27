<?php

namespace App\Exceptions;

use Exception;

class ValidationDateException extends Exception
{
    public function render()
    {
        return responseValidate([], $this->getMessage());
    }
}
