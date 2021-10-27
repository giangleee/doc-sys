<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;


class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'email' => 'required|email|max:255',
            'avatar' => "file|max:1024|mimes:png,jpeg,jpg,gif",
            'full_name' => 'nullable|string|max:50',
            'katakana_name' => 'nullable|string|max:50',
            'phone' => 'nullable|numeric|digits_between:9,13',
        ];
    }
    public function messages()
    {
        return [
            'name.required'=>__('message.profiles.name.required'),
            'name.max'=>__('message.profiles.name.max'),
            'email.required'=>__('message.profiles.email.required'),
            'email.email'=>__('message.profiles.email.email'),
            'email.max'=>__('message.profiles.email.max'),
            'avatar.max'=>__('message.profiles.avatar.max'),
            'avatar.mimes'=>__('message.profiles.avatar.mimes'),
            'full_name'=>__('message.profiles.full_name.max'),
            'katakana_name'=>__('message.profiles.katakana_name.max'),
            'katakana_name.regex'=>__('message.regex'),
            'phone.numeric'=>__('message.profiles.phone.numeric'),
            'phone.digits_between'=>__('message.profiles.phone.digits_between'),
        ];
    }
}
