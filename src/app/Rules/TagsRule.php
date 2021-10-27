<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TagsRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $arrTags = explode(',', $value);
        foreach ($arrTags as $key => $arrTag) {
            if (empty($arrTag)) {
                unset($arrTags[$key]);
            }
        }
        $numberTag = count($arrTags);
        return $numberTag <= 10;

    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return __('message.tags.total');
    }
}
