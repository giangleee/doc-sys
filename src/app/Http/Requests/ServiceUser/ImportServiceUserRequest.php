<?php

namespace App\Http\Requests\ServiceUser;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MaxFileUploadRule;

class ImportServiceUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'files' => new MaxFileUploadRule(2),
            'files.*' => [
                'required',
                'file',
                'mimes:csv,txt,xls,xlsx',
            ],
        ];
    }

    public function messages()
    {
        return [
            'files.0.mimes' => __(
                'message.service_user.file_upload_invalid',
                ['filename' => $this->file('files')[0]->getClientOriginalName()]
            ),
            'files.1.mimes' => __(
                'message.service_user.file_upload_invalid',
                ['filename' => $this->file('files')[1]->getClientOriginalName()]
            ),
        ];
    }
}
