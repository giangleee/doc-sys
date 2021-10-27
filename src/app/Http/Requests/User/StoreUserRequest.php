<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreUserRequest extends FormRequest
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
            'role_id' => 'required',
            'branch_id' => 'required',
            'name' => 'required|max:50',
            'employee_id' => 'required|max:20|unique:users,employee_id,NULL,id,deleted_at,NULL|regex:/^[^<>"\']+$/',
            'email' => 'required|email|max:255',
            'phone' => 'numeric',
            'position_id' => 'required',
            'division_id' => 'required_if:role_id,4',
            'office_id' => 'required_if:role_id,4',
            'store_id' => 'required_if:role_id,4'
        ];
    }

    public function messages()
    {
        return [
            'employee_id.regex' => __('message.users.employee_id.regex'),
            'office_id.required_if' => __('message.users.office_id.required'),
            'name.required' => __('message.users.name.required'),
            'name.max' => __('message.users.name.max'),
            'employee_id.required' => __('message.users.employee_id.required'),
            'employee_id.unique' => __('message.users.employee_id.unique'),
            'employee_id.max' => __('message.users.employee_id.max'),
            'email.required' => __('message.users.email.required'),
            'email.email' => __('message.users.email.email'),
            'email.unique' => __('message.users.email.unique'),
            'email.max' => __('message.users.email.max'),
            'phone.numeric' => __('message.users.phone.numeric'),
            'position_id.required' => __('message.users.position_id.required'),
            'branch_id.required' => __('message.users.branch_id.required'),
            'division_id.required_if' => __('message.users.division_id.required'),
            'store_id.required_if' => __('message.users.store_id.required'),
        ];
    }
}
