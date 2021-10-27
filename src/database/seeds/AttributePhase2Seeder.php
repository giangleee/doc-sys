<?php

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\DocumentTypeAttribute;
use App\Models\DocumentType;
use App\Helper\Constant;
use Carbon\Carbon;

class AttributePhase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentTypes = DocumentType::all();
        $documentTypeAttribute = new DocumentTypeAttribute;
        $attributes = Attribute::all();
        $attribute = Attribute::find(Attribute::STORAGE_PERIOD);

        if (empty($attribute)) {
            $storagePeriod = Attribute::create([
                'code' => Attribute::STORAGE_PERIOD,
                'name' => '保管期限',
                'type' => Attribute::IS_DATETIME_RANGE,
            ]);

            foreach ($documentTypes as $documentType) {
                if ($documentType->code != Constant::DOCUMENT_HAVE_NOT_ATTRIBUTE) {

                    if (
                        $documentType->code == DocumentType::SERVICED_ELDERLY_HOUSING ||
                        $documentType->code == DocumentType::WELFARE_CENTER ||
                        $documentType->code == DocumentType::HOUSING_SUPPORT ||
                        $documentType->code == DocumentType::CHANGE_REGISTATION ||
                        $documentType->code == DocumentType::SIGN_UP_FOR_SUPPORT_PROJECTS
                    ) {
                        foreach ($attributes as $recordAttribute) {
                            DocumentTypeAttribute::firstOrCreate(
                                [
                                    'attribute_id' => $recordAttribute->id,
                                    'document_type_id' => $documentType->id
                                ],
                                [
                                    'attribute_id' => $recordAttribute->id,
                                    'document_type_id' => $documentType->id,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                        }
                    }

                    $dataSave[] = [
                        'attribute_id' => $storagePeriod->id,
                        'document_type_id' => $documentType->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            $documentTypeAttribute::insert($dataSave);
        }
    }
}
