<?php

namespace Puffy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestRequest extends FormRequest
{
    public function rules()
    {
        return [
            'test' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'test' => '画像',
        ];
    }

    public function messages()
    {
        return [
            'test.required' => __('validation.custom.test_required'),
        ];
    }
}
