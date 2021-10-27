<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxUploadedFileEditDocumentRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $filesInfo = $value;
        $numberFile = 0;
        foreach ($filesInfo as $fileInfo) {
           if (is_file($fileInfo['file'])) {
               $numberFile++;
           }
        }
        return $numberFile <= 30;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return  __('message.files.max_file_upload');
    }
}
