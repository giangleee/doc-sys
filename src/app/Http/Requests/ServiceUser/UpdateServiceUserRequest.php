<?php

namespace App\Http\Requests\ServiceUser;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceUserRequest extends FormRequest
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
            'name' => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('message.service_user.name.required'),
            'name.max' => __('message.service_user.name.max'),
        ];
    }
}
