<?php

namespace App\Rules;

use App\Repositories\DocumentObjectRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentTypeRepository;
use Illuminate\Contracts\Validation\Rule;

class CheckObjectDuplicateRule implements Rule
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
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (isset(request()->service_user) && !empty(request()->service_user)) {
            $dataServiceUser = json_decode(request()->service_user, 1);
            if (isset($dataServiceUser['id']) && !empty($dataServiceUser['id'])) {
                // get info document object
                $documentObjectRepository = new DocumentObjectRepository();
                $documentObject = $documentObjectRepository->findByCode($value);

                // get document type
                $documentTypeRepository = new DocumentTypeRepository();
                $documentType = $documentTypeRepository->findByCode(request()->document_type);
                $documentTypeId = $documentType->id;

                $serviceUserId = $dataServiceUser['id'];
                $documentRepository = new DocumentRepository();
                $documentInFileSet = $documentRepository->getDocumentsInFileSet(
                    $serviceUserId,
                    $documentTypeId,
                    request()->office_id
                );
                $objectBelongToFileSet = array_unique($documentInFileSet->pluck('document_object_id')->toArray());

                return !in_array($documentObject->id, $objectBelongToFileSet);
            }
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
