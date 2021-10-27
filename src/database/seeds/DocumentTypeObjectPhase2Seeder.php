<?php

use Illuminate\Database\Seeder;
use App\Models\DocumentTypeObject;
use App\Models\DocumentType;
use App\Models\DocumentObject;
use App\Helper\Constant;

class DocumentTypeObjectPhase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentTypes = DocumentType::all();
        $documentObjects = DocumentObject::all()->pluck('code', 'id')->toArray();
        $documentObjectFindByCode = DocumentObject::where('code', '000009')->first();
        $documentObjectTemp = DocumentObject::where('code', Constant::CODE_UPDATE_HOME_NURSING)->first();
        $documentTypeFindByCode = DocumentType::where('code', '0002')->first();
        $documentTypeWelfare = DocumentType::where('code', DocumentType::WELFARE_CENTER)->first();
        $objectsRemoveOutOfWelfare = DocumentObject::whereIn('code', ['000048', '000008', '000009', '000033'])
            ->pluck('id')->toArray();
        foreach ($documentTypes as $documentType) {
            $documentObjectByDocumentType = Constant::DOCUMENT_TYPE_OBJECT[$documentType->code];
            $documentObjectIds = array_keys(array_intersect($documentObjects, $documentObjectByDocumentType));
            foreach ($documentObjectIds as $documentObjectId) {
                DocumentTypeObject::firstOrCreate(
                    [
                        'document_type_id'=> $documentType->id,
                        'document_object_id'=> $documentObjectId
                    ],
                    [
                        'document_type_id'=> $documentType->id,
                        'document_object_id'=> $documentObjectId
                    ]
                );
            }
        }

        $dataDocumentObj['document_object_id'] = $documentObjectTemp->id;
        DocumentTypeObject::where('document_type_id', $documentTypeFindByCode->id)
            ->where('document_object_id', $documentObjectFindByCode->id)->update($dataDocumentObj);
        if (!empty($documentTypeWelfare) && !empty($objectsRemoveOutOfWelfare)) {
            DocumentTypeObject::where('document_type_id', $documentTypeWelfare->id)
                ->whereIn('document_object_id', $objectsRemoveOutOfWelfare)->delete();
        }
    }
}
