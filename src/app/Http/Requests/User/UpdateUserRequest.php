<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateUserRequest extends FormRequest
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
    public function rules(Request $request)

    {
        $rules = [
            'role_id' => 'required',
            'branch_id' => 'required',
            'division_id' => 'required_if:role_id,4',
            'office_id' => 'required_if:role_id,4',
            'store_id' => 'required_if:role_id,4',
            'name' => 'required|max:50|regex:/^[^<>"\']+$/',
            'employee_id' => 'required|max:20|regex:/^[^<>"\']+$/|unique:users,employee_id,'.$this->route('id'),
            'email' => 'required|max:50|email',
            'phone' => 'nullable|numeric'
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'name.regex' => __('message.regex'),
            'employee_id.regex' => __('message.regex'),
            'employee_id.required' => __('message.users.employee_id.required'),
            'employee_id.max' => __('message.users.employee_id.max'),
            'branch_id.required' => __('message.users.branch_id.required'),
            'division_id.required_if' => __('message.users.division_id.required'),
            'office_id.required_if' => __('message.users.office_id.required'),
            'store_id.required_if' => __('message.users.store_id.required'),
            'name.required' => __('message.users.name.required'),
            'name.max' => __('message.users.name.max'),
            'email.required' => __('message.users.email.required'),
            'email.max' => __('message.users.email.max'),
            'email.unique' => __('message.users.email.unique'),
            'email.email' => __('message.users.email.email'),
            'phone.numeric' =>  __('message.users.phone.numeric'),
        ];
    }
}
