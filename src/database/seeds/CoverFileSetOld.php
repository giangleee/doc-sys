<?php

use Illuminate\Database\Seeder;
use App\Models\FileSetPermission;
use App\Models\Document;
use App\Models\FileSetManagement;
use App\Models\Folder;
use App\Models\ServiceUser;
use App\Models\DocumentType;
use App\Models\DocumentObject;
use App\Repositories\FolderRepository;

class CoverFileSetOld extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //get service user id
        $serviceUserIds = FileSetPermission::where('service_user_id', '<>', 0)->pluck('service_user_id');

        //get documents
        $documents = Document::whereIn('service_user_id', $serviceUserIds)->get();

        //set data file set
        $dataFileSets = [];

        foreach ($documents as $document) {
            $dataFileSets[] = [
                'service_user_id' => $document->service_user_id,
                'document_type_id' => $document->document_type_id,
                'office_id' => $document->office_id,
                'document_id' => $document->id,
                'document_object_id' => $document->document_object_id
            ];
        }

        //cover data file set
        foreach ($dataFileSets as $value) {
            $folder = Folder::where('office_id', $value['office_id'])->first();
            $serviceUser = ServiceUser::find($value['service_user_id']);
            $documentType = DocumentType::find($value['document_type_id']);
            $documentObject = DocumentObject::find($value['document_object_id']);

            //set data create folder
            $dataCreateFolder = [
                'service_user_id' => $serviceUser->id,
                'service_user_name' => $serviceUser->name,
                'service_user_code' => $serviceUser->code,
                'office_id' => $folder->office->id,
                'document_type_id' => $value['document_type_id'],
                'document_type' => $documentType->code,
                'office_name' => $folder->office->name,
                'document_object' => $documentObject->code
            ];

            //set data create file set management
            $dataFileSetManagement = [
                'service_user_id' => $value['service_user_id'],
                'office_id' => $value['office_id'],
                'document_type_id' => $value['document_type_id']
            ];

            //Create file set management and file set permission
            $fileSetManagement = FileSetManagement::firstOrCreate(
                $dataFileSetManagement, $dataFileSetManagement

            );

            FileSetPermission::where('service_user_id', $fileSetManagement->service_user_id)
                ->where('service_user_id', $fileSetManagement->service_user_id)
                ->update(
                    ['file_set_management_id' => $fileSetManagement->id]
                );

            //Create folder
            $folderRepo = new FolderRepository;
            $folderUpdate = $folderRepo->findOrCreateFolderForServiceUser($dataCreateFolder);

            //update document
            $dataDocument['folder_id'] = $folderUpdate->id;
            Document::find($value['document_id'])->update($dataDocument);

        }
    }
}
