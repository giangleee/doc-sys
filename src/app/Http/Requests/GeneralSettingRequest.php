<?php


namespace App\Http\Requests;


use App\Models\GeneralSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralSettingRequest extends FormRequest
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
            'send_alert' => [
                'required',
                Rule::in([GeneralSetting::SEND_ALERT_OFF, GeneralSetting::SEND_ALERT_ON])
            ],
            'exchange_deadline' => 'nullable|date|date-format:Y-m-d'
        ];
    }
}
