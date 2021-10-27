<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsFileValidRule implements Rule
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
            return in_array(strtolower($value->getClientOriginalExtension()), ['pdf','docx','doc','png','jpeg','jpg']);
        }
        return true;
    }

    /**
     * Get the validation error messages
     */
    public function message()
    {
        return __('message.files.mimes');
    }
}
