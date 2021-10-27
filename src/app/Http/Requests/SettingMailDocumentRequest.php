<?php

namespace App\Http\Requests;

use App\Models\MailDocument;
use App\Rules\IsEmailValid;
use Illuminate\Foundation\Http\FormRequest;

class SettingMailDocumentRequest extends FormRequest
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
            'mail_template' => 'required_if:send_type,' . MailDocument::SEND_TYPE_MANUAL,
            'bcc' => ['nullable', new IsEmailValid()],
            'send_date' => 'required_if:send_type,' . MailDocument::SEND_TYPE_MANUAL,
            'send_unit' => 'required_if:send_interval,' . MailDocument::REPEAT,
            'send_value' => 'required_if:send_interval,' . MailDocument::REPEAT
        ];
    }
}
