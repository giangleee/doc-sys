<?php

namespace App\Rules;

use App\Repositories\DocumentRepository;
use App\Repositories\DocumentTypeRepository;
use App\Repositories\ServiceUserRepository;
use Illuminate\Contracts\Validation\Rule;

class CheckFileSetIsFullRule implements Rule
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
        if (isset(request()->service_user) && !empty(request()->service_user)) {
            $dataServiceUser = json_decode(request()->service_user, 1);
            if (isset($dataServiceUser['id']) && !empty($dataServiceUser['id'])) {
                // get document type
                $documentTypeRepository = new DocumentTypeRepository();
                $documentType = $documentTypeRepository->findByCode(request()->document_type);
                $documentTypeId = $documentType->id;
                $documentTypeObjects = $documentType->objects()->pluck('document_object_id')->toArray();

                $serviceUserId = $dataServiceUser['id'];
                $documentRepository = new DocumentRepository();
                $documentInFileSet = $documentRepository->getDocumentsInFileSet($serviceUserId, $documentTypeId);
                $objectsBelongToFileSet = array_unique($documentInFileSet->pluck('document_object_id')->toArray());

                return count(array_diff($documentTypeObjects, $objectsBelongToFileSet)) > 0;
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
        return __('message.fileset.is_full');
    }
}
