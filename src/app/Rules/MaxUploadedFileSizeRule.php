<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxUploadedFileSizeRule implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        if (is_file($value)) {
            return ($value->getSize() / 1024) <= 5120;
        }
        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return __('validation.max.file');
    }
}
