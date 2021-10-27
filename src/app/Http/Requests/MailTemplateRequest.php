<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailTemplateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'subject' => 'required|max:50',
            'body' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('message.template.name.required'),
            'name.max' => __('message.template.name.max'),
            'subject.required' => __('message.template.subject.required'),
            'subject.max' => __('message.template.subject.max'),
            'body.required' => __('message.template.body.required'),
            'body.max' => __('message.template.body.max'),
        ];
    }

}
