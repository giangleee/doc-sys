<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            'name' => 'required|max:255|regex:/^[^<>"\']+$/'
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => __('message.tags.name.regex')
        ];
    }
}
