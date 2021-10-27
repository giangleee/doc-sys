<?php

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Helper\Constant;
use Carbon\Carbon;

class DocumenTypePhase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentTypes = Constant::DOCUMENT_TYPES;
        $sortDocumentTypes = Constant::SORT_DOCUMENT_TYPES;
        foreach ($documentTypes as $code => $documentType) {
            DocumentType::updateOrInsert(
                ['code' => $code],
                [
                    'name' => $documentType,
                    'code' => $code,
                    'pattern_type' => $code == Constant::DOCUMENT_IS_TEMPLATE
                        ? DocumentType::IS_TEMPLATE
                        : DocumentType::IS_NOT_TEMPLATE,
                    'type' => in_array($code, Constant::IS_B2C) ? DocumentType::B2C : DocumentType::B2B,
                    'sort' => $sortDocumentTypes[$code],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
