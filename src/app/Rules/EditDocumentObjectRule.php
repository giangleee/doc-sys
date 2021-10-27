<?php

namespace App\Rules;

use App\Models\DocumentObject;
use App\Repositories\DocumentObjectRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentTypeRepository;
use Illuminate\Contracts\Validation\Rule;

class EditDocumentObjectRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $documentRepository = new DocumentRepository();
        $document = $documentRepository->findOrFail(request()->id);
        // get info document object
        $documentObjectRepository = new DocumentObjectRepository();
        $documentObject = $documentObjectRepository->findByCode($value);
        $serviceUserId = $document->service_user_id;
        $documentTypeId = $document->document_type_id;

        $documentsInFileSet = $documentRepository->getDocumentsInFileSet(
            $serviceUserId,
            $documentTypeId,
            request()->office_id
        );

        $objectBelongToFileSet = array_unique($documentsInFileSet->pluck('document_object_id')->toArray());

        if ($documentObject->id != $document->document_object_id) {
            return !in_array($documentObject->id, $objectBelongToFileSet);
        }

        if (
            request()->office_id != $document->store_id &&
            !in_array($documentObject->code, DocumentObject::ARR_HOME_CARE_AND_HOME_NURSING) &&
            !in_array($documentObject->code, DocumentObject::ARR_WELFARE_CENTER)
        ) {
            return !in_array($documentObject->id, $objectBelongToFileSet);
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.fileset.duplicated');
    }
}
