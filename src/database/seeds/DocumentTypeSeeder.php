<?php

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Helper\Constant;
use Carbon\Carbon;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentType::truncate();

        $documentTypes = Constant::DOCUMENT_TYPES;
        $dataSave = [];
        foreach ($documentTypes as $code => $documentType) {
            $dataSave[] = [
                'name' => $documentType,
                'code' => $code,
                'pattern_type' => $code == Constant::DOCUMENT_IS_TEMPLATE ? DocumentType::IS_TEMPLATE : DocumentType::IS_NOT_TEMPLATE,
                'type' => in_array($code, Constant::IS_B2C) ? DocumentType::B2C : DocumentType::B2B,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        DocumentType::insert($dataSave);
    }
}
