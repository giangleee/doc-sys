<?php

namespace App\Exceptions;

use Exception;

class ServerError extends Exception
{
    public function render()
    {
        $message = empty($this->getMessage()) ? __('message.server_error') : $this->getMessage();
        return responseError(500, $message);
    }
}
