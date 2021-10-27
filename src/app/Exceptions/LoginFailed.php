<?php

namespace App\Exceptions;

use Exception;

class LoginFailed extends Exception
{
    public function render()
    {
        return responseError(422, '', ['unauthenticate' => $this->getMessage()]);
    }
}
