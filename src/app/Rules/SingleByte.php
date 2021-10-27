<?php

namespace Royal\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Helper\Constant;

class SingleByte implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match(Constant::REGEX_NOT_JAPANESE, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.single_byte');
    }
}
