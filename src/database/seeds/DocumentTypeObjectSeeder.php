<?php

use Illuminate\Database\Seeder;
use App\Models\DocumentTypeObject;
use App\Models\DocumentType;
use App\Models\DocumentObject;
use App\Helper\Constant;

class DocumentTypeObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentTypeObject::truncate();
        $documentTypes = DocumentType::all();
        $documentObjects = DocumentObject::all()->pluck('code', 'id')->toArray();

        foreach ($documentTypes as $documentType) {
            $documentObjectByDocumentType = Constant::DOCUMENT_TYPE_OBJECT[$documentType->code];
            $documentTypeObject = array_keys(array_intersect($documentObjects, $documentObjectByDocumentType));
            $documentType->objects()->sync($documentTypeObject);
        }
    }
}
