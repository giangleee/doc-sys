<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxFileUploadRule implements Rule
{
    private $total = 0;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($total)
    {
        $this->total = $total;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $totalFile = count(request()->file('files'));
        if ($this->total != $totalFile) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.service_user.max_file_upload', ['maxfile' => $this->total]);
    }
}
