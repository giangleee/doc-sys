<?php

namespace App\Http\Requests\Folder;

use App\Repositories\FolderRepository;
use App\Rules\UpdateFolderRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateFolderRequest extends FormRequest
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
            'name' => ['required', new UpdateFolderRule(), 'max:50'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('message.folder.name.required'),
            'name.max' => __('message.folder.name.max'),
        ];
    }
}
