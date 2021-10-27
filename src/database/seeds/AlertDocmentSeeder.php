<?php

use Illuminate\Database\Seeder;

use App\Models\Document;
use App\Models\DocumentTypeObject;
use App\Models\DocumentType;
use App\Models\DocumentObject;
use App\Models\Office;
use App\Models\ServiceUser;
use App\Models\DocumentAttribute;
use App\Models\Attribute;
use App\Models\MailDocument;
use App\Repositories\MailDocumentRepository;
use App\Repositories\RoleRepository;

class AlertDocumentSeeder extends Seeder
{

    protected $startDate = '2020-12-01';
    protected $beforeExpiry = '2020-12-31';
    protected $expired = '2021-01-12';
    protected $outOfDate = '2021-02-01';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mailDocumentRepo = new MailDocumentRepository();
        $documentTypeObject = DocumentTypeObject::all();
        $office = Office::all()->pluck('id')->toArray();
        $serviceUser = ServiceUser::all()->pluck('id')->toArray();
        $documentObject = DocumentObject::all()->pluck('name', 'id')->toArray();
        $attributeID = Attribute::where('code', Attribute::VALIDITY_PERIOD)->first()->id;

        for ($i = 1; $i <= 3; $i++) {
            foreach ($documentTypeObject as $item) {
                $officeKey = array_rand($office);
                $serviceUseKey = array_rand($serviceUser);
    
                //insert document
                $document = new Document;
                $document->office_id = $office[$officeKey];
                $document->owner_id = 1;
                $document->document_type_id = $item->document_type_id;
                if ($item->document_type_id > 2) {
                    $document->partner_name = 'Test send mail alert ' . $i;
                }
                if ($item->document_type_id == 1 || $item->document_type_id == 2) {
                    $document->document_object_id = $item->document_object_id;
                    $document->service_user_id = $serviceUser[$serviceUseKey];
                }
                $document->name = 'Alert_' . $documentObject[$item->document_object_id] . '_' . $i;
                $document->save();

                //insert document attribute
                $documentAtt = new DocumentAttribute;
                $documentAtt->document_id = $document->id;
                $documentAtt->attribute_id = $attributeID;
                $documentAtt->start_date = $this->startDate;
                if ($i == 1) {
                    $documentAtt->end_date = $this->beforeExpiry;
                }
                if ($i == 2) {
                    $documentAtt->end_date = $this->expired;
                }
                if ($i == 3) {
                    $documentAtt->end_date = $this->outOfDate;
                }
                $documentAtt->save();

                //insert document alert
                $mailDocumentRepo->saveMailDocument($document);
            }
        }
    }
}
