<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function render()
    {
        return responseError(400, $this->getMessage());
    }
}
