<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|confirmed|min:8|max:16'
        ];
    }

    public function attributes()
    {
        return [
            'password' => __('labels.login.password'),
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('message.login.password.required'),
            'password.min' => __('message.login.password.min'),
            'password.max' => __('message.login.password.max'),
            'password.confirmed' => __('message.reset_password.confirmed')
        ];
    }
}
