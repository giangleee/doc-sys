<?php

namespace App\Http\Requests\Folder;

use App\Rules\StoreFolderRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['required',new StoreFolderRule(),'max:50']
        ];
    }

    public function messages()
    {
        return [
            'name.required'=>__('message.folder.name.required'),
            'name.max'=>__('message.folder.name.max'),
        ];
    }

}
