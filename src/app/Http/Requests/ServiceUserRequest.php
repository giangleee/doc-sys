<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUserRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|unique:service_users,code,NULL,id,deleted_at,NULL|max:20|regex:/^[^<>"\']+$/',
            'name' => 'required|max:50',
        ];
    }
    public function messages()
    {
        return [
            'code.regex' => __('message.regex'),
            'code.required' => __('message.service_user.code.required'),
            'code.unique' => __('message.service_user.code.unique'),
            'code.max' => __('message.service_user.code.max'),
            'name.required' => __('message.service_user.name.required'),
            'name.max' => __('message.service_user.name.max'),
        ];
    }
}
