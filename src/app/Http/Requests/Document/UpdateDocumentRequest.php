<?php

namespace App\Http\Requests\Document;

use App\Helper\Constant;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentTypeRepository;
use App\Rules\EditDocumentObjectRule;
use App\Rules\IsFileValidRule;
use App\Rules\MaxUploadedFileEditDocumentRule;
use App\Rules\RequiredObjectWithFileSetRule;
use App\Rules\SumUploadedFileSizeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
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
            'branch_id' => 'required',
            'division_id' => 'required',
            'office_id' => 'required',
            'store_id' => 'required',
            'name' => 'required|max:50',
            'files_info' => ['required', new SumUploadedFileSizeRule(),new MaxUploadedFileEditDocumentRule()],
            'files_info.*.file' => ['required', new IsFileValidRule(),'max:5120'],
            'document_object' => [
                Rule::requiredIf(function () {
                    $documentRepository = new DocumentRepository();
                    $document = $documentRepository->findOrFail(request()->id);

                    $documentTypeRepository = new DocumentTypeRepository();
                    $documentType = $documentTypeRepository->findOrFail($document->document_type_id);

                    return in_array($documentType->code, Constant::IS_B2C);
                }),
                new EditDocumentObjectRule()
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('message.document.name.required'),
            'name.max' => __('message.document.name.max'),
            'files_info.required' => __('message.document.files_info.required'),
            'files_info.*.file.required' => __('message.document.files_info.required'),
            'files_info.*.file.max' => __('message.document.files_info.max'),
            'branch_id.required'=>__('message.document.branch.required'),
            'division_id.required'=>__('message.document.division.required'),
            'office_id.required'=>__('message.document.office.required'),
            'store_id.required'=>__('message.document.store.required'),
            'document_object.required' => __('message.document.document_object.required_if')
        ];
    }
}
