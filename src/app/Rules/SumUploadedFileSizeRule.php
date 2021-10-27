<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SumUploadedFileSizeRule implements Rule
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
        $totalSize = array_reduce($value, function ($sum, $item) {
            if (isset($item['file']) && is_file($item['file'])) {
                $sum += ($item['file']->getSize() / 1024);
                return $sum;
            }
            return $sum;
        });
        return $totalSize <= 51200;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return __('message.files.sum_uploaded_file_size');
    }
}
