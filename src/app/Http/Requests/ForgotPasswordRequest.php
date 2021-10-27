<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'employee_id' => 'required|max:20',
        ];
    }

    public function attributes()
    {
        return [
            'employee_id' => __('labels.login.id')
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => __('message.login.ID.required'),
            'employee_id.max' => __('message.login.ID.max'),
        ];
    }
}
