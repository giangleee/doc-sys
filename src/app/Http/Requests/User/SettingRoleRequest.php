<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SettingRoleRequest extends FormRequest
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
            'role_id' => 'required'
        ];
    }
}
