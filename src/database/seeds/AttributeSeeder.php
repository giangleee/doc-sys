<?php

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\DocumentTypeAttribute;
use App\Models\DocumentType;
use App\Helper\Constant;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attribute::truncate();
        DocumentTypeAttribute::truncate();

        $executed = Attribute::create([
            'code' => Attribute::EXECUTED,
            'name' => '締結状態',
            'type' => Attribute::IS_RADIO,
            'value' => json_encode([
                [
                    'id' => 1,
                    'name' => '締結済み'
                ],
                [
                    'id' => 2,
                    'name' => '未締結'
                ]
            ])
        ]);
        $executionDate = Attribute::create([
            'code' => Attribute::EXECUTION_DATE,
            'name' => '締結日',
            'type' => Attribute::IS_DATE
        ]);
        $note = Attribute::create([
            'code' => Attribute::NOTE,
            'name' => '備考',
            'type' => Attribute::IS_TEXTAREA
        ]);
        $formPeriod = Attribute::create([
            'code' => Attribute::FORM_PERIOD,
            'name' => '帳票期間',
            'type' => Attribute::IS_DATETIME_RANGE
        ]);
        $supplyPeriod = Attribute::create([
            'code' => Attribute::SUPPLY_PERIOD,
            'name' => '支給期間',
            'type' => Attribute::IS_DATETIME_RANGE
        ]);

        $validityPeriod = Attribute::create([
            'code' => Attribute::VALIDITY_PERIOD,
            'name' => '有効期間',
            'type' => Attribute::IS_DATETIME_RANGE,
        ]);

        $documentTypes = DocumentType::all();
        foreach ($documentTypes as $documentType) {
            if ($documentType->code != Constant::DOCUMENT_HAVE_NOT_ATTRIBUTE) {
                $documentType->attributes()->sync([
                    $executed->id,
                    $executionDate->id,
                    $note->id,
                    $formPeriod->id,
                    $supplyPeriod->id,
                    $validityPeriod->id
                ]);
            }
        }
    }
}
