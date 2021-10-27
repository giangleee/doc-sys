<?php

namespace App\Http\Requests\Document;

use App\Helper\Constant;
use App\Rules\CheckFileSetIsFullRule;
use App\Rules\CheckObjectDuplicateRule;
use App\Rules\SumUploadedFileSizeRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:50',
            'document_type' => 'required',
            'service_user' => 'required_if:document_type,'. implode(',', Constant::IS_B2C),
            'partner_name' => 'max:64|required_if:document_type,'. Constant::DOCUMENT_IS_TEMPLATE,
            'files_info' => ['required', new SumUploadedFileSizeRule(),'max:30'],
            'files_info.*.file' => 'required|file|max:5120|mimes:pdf,docx,doc,png,jpeg,jpg',
            'document_object' => [
                'required_if:document_type,'. implode(',', Constant::IS_B2C),
                new CheckFileSetIsFullRule(),
                new CheckObjectDuplicateRule()
            ],
        ];
        if (!auth()->user()->isStaff()) {
            $rules['branch_id'] = 'required';
            $rules['division_id'] = 'required';
            $rules['office_id'] = 'required';
            $rules['store_id'] = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required'=>__('message.document.name.required'),
            'name.max'=>__('message.document.name.max'),
            'document_type.required' => __('message.document.document_type.required'),
            'service_user.required_if' => __('message.service_user.required_if'),
            'partner_name.required_if' => __('message.partner_name.required_if'),
            'files_info.required' => __('message.document.files_info.required'),
            'files_info.max' => __('message.files.max_file_upload'),
            'files_info.*.file.required' => __('message.document.files_info.required'),
            'files_info.*.file.max' => __('message.document.files_info.max'),
            'files_info.*.file.mimes' => __('message.files.mimes'),
            'branch_id.required'=>__('message.document.branch.required'),
            'division_id.required'=>__('message.document.division.required'),
            'office_id.required'=>__('message.document.office.required'),
            'document_object.required_if' => __('message.document.document_object.required_if')
        ];
    }
}
