<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Helper\Constant;

class RegexPassword implements Rule
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
        return (preg_match(Constant::REGEX_PASSWORD, $value) && preg_match(Constant::REGEX_NOT_JAPANESE, $value));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.regex', ['attribute' => 'パスワード']);
    }
}
