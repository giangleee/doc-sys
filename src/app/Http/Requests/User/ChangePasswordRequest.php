<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'user_id' => 'required',
            'password' => 'required|confirmed|min:8|max:16',

        ];
    }
    public function messages()
    {
        return [
           'user_id.required' => __('message.password.user_id.required'),
            'password.required' => __('message.password.password.required'),
            'password.confirmed' => __('message.password.password.confirmed'),
            'password.min' => __('message.profiles.new_password.min'),
            'password.max' => __('message.profiles.new_password.max'),
        ];
    }
}
