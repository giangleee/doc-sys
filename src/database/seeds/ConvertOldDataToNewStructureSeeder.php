<?php

use Illuminate\Database\Seeder;

use App\Services\FileService;
use App\Repositories\DocumentRepository;
use App\Repositories\FileSetManagementRepository;
use App\Repositories\ServiceUserRepository;
use App\Repositories\OfficeRepository;
use App\Repositories\DocumentTypeRepository;
use App\Repositories\FileSetPermissionRepository;
use App\Repositories\FileRepository;
use App\Repositories\FolderRepository;
use App\Repositories\DocumentObjectRepository;
use App\Models\DocumentType;
use App\Models\Folder;

use App\Helper\Constant;
use Carbon\Carbon;

class ConvertOldDataToNewStructureSeeder extends Seeder
{
    protected $positionOffice = 5;
    protected $positionDocumentName = 7;
    protected $positionFilename = 8;
    protected $path = 'old/version1';
    protected $fileService;
    protected $documentRepo;
    protected $filesetManageRepo;
    protected $serviceUserRepo;
    protected $officeRepo;
    protected $documentTypeRepo;
    protected $filesetPermissionRepo;
    protected $fileRepo;
    protected $folderRepo;
    protected $documentObjectRepo;
    protected $serviceUserPermission;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->documentRepo = new DocumentRepository();
        $this->filesetManageRepo = new FileSetManagementRepository();
        $this->serviceUserRepo = new ServiceUserRepository();
        $this->officeRepo = new OfficeRepository();
        $this->documentTypeRepo = new DocumentTypeRepository();
        $this->filesetPermissionRepo = new FileSetPermissionRepository();
        $this->fileRepo = new FileRepository();
        $this->folderRepo = new FolderRepository();
        $this->documentObjectRepo = new DocumentObjectRepository();
        $this->serviceUserPermission = [];
    }

    private function convertData($documents)
    {
        foreach ($documents as $item) {
            //get info document type
            $documentType = $this->documentTypeRepo->find($item->document_type_id);
            $documentObjectType = $this->documentObjectRepo->find($item->document_object_id);
            $serviceUser = $this->serviceUserRepo->find($item->service_user_id);
            $officeInfo = $this->officeRepo->find($item->office_id);

            //fileset management
            $dataFilesetManage = [
                'service_user_id' => $item->service_user_id,
                'office_id' => $item->office_id,
                'document_type_id' => $item->document_type_id
            ];
            $filesetManage = $this->filesetManageRepo->firstOrCreateData($dataFilesetManage, $dataFilesetManage);

            //fileset permission
            $filesetPermissionWithManage = $this->filesetPermissionRepo->filterFirst([
                'service_user_id' => $item->service_user_id,
                'file_set_management_id' => $filesetManage->id
            ]);
            if (!$filesetPermissionWithManage) {
                if (!isset($this->serviceUserPermission[$item->service_user_id])) {
                    $this->serviceUserPermission[$item->service_user_id] = $this->filesetPermissionRepo->filter([
                        'service_user_id' => $item->service_user_id,
                        'file_set_management_id' => 0
                    ]);

                    //delete fileset permission
                    $this->filesetPermissionRepo->deleteByServiceUserWithoutManage($item->service_user_id);
                }

                //insert fileset permission
                foreach ($this->serviceUserPermission[$item->service_user_id] as $filetsetPermission) {
                    $this->filesetPermissionRepo->create([
                        'file_set_management_id' => $filesetManage->id,
                        'office_id' => $filetsetPermission->office_id,
                        'positions_id' => $filetsetPermission->positions_id
                    ]);
                }
            }

            //folder
            $isCommon = in_array($documentObjectType->code, DocumentType::DOCUMENT_COMMON[$documentType->code]) ? 1 : 0;
            $folderOffice = $this->folderRepo->filterFirst([
                'office_id' => $item->office_id,
                'is_system' => Folder::IS_SYSTEM
            ]);
            $folderServiceUser = $this->folderRepo->firstOrCreateData(
                [
                    'service_user_id' => $item->service_user_id,
                    'parent_id' => $folderOffice->id,
                    'is_system' => Folder::IS_SYSTEM
                ],
                [
                    'service_user_id' => $item->service_user_id,
                    'parent_id' => $folderOffice->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'name' => $serviceUser->code . 'ー' . $serviceUser->name,
                    'owner_id' => 1,
                ]
            );
            if ($isCommon) {
                //add or update folder common
                $folder = $this->folderRepo->firstOrCreateData(
                    [
                        'parent_id' => $folderServiceUser->id,
                        'is_common' => Folder::IS_COMMON
                    ],
                    [
                        'parent_id' => $folderServiceUser->id,
                        'is_common' => Folder::IS_COMMON,
                        'is_system' => Folder::IS_SYSTEM,
                        'name' => Folder::NAME_FOLDER_COMMON,
                        'owner_id' => 1,
                    ]
                );
            } else {
                $folder = $this->folderRepo->firstOrCreateData(
                    [
                        'parent_id' => $folderServiceUser->id,
                        'document_type_id' => $item->document_type_id
                    ],
                    [
                        'parent_id' => $folderServiceUser->id,
                        'is_system' => Folder::IS_SYSTEM,
                        'name' => $documentType->name . '(' . $officeInfo->name . ')',
                        'owner_id' => 1,
                    ]
                );
            }

            $this->documentRepo->find($item->id)->update(['folder_id' => $folder->id]);
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //get data
        $offices = $this->officeRepo->getAll()->pluck('id', 'code')->toArray();
        $files = $this->fileService->getAllFile($this->path);

        //convert hiragicode to office code
        $hiragicodeWithOffice = convertHiragicodeToOffice();

        //get info folder in s3
        $folderS3 = [];
        foreach ($files as $item) { 
            $file = explode('/', $item);
            $folderS3[$file[$this->positionDocumentName]][] = $item;
        }

        //handel data
        foreach ($folderS3 as $documentName => $file) { 
            //convert document name
            if (isset(Constant::TRANSFER_VERSION_1[$documentName]) && Constant::TRANSFER_VERSION_1[$documentName]) {
                $documentName = Constant::TRANSFER_VERSION_1[$documentName];
            } 

            //get office with document name
            $office = str_replace(['＿', '_', '‗', '₋'], '_', $documentName);
            $office = explode('_', $office);
            $office = $hiragicodeWithOffice[$office[count($office) - 1]] ?? '';
            $officeID = (isset($offices[$office]) && $offices[$office]) ? $offices[$office] : false;            
            if (!$officeID) {
                continue;
            }

            //get document ID
            $documentID = $this->fileRepo->getDocumentID($file);
            if (empty($documentID)) {
                continue;
            }

            //convert document old
            $documents = $this->documentRepo->getDocumentByIDWithoutAttr($documentID);
            $this->convertData($documents);
        }

        //convert document new
        $documentID = $this->fileRepo->getDocumentNew($this->path);
        if (!empty($documentID)) {
            $documents = $this->documentRepo->getDocumentByIDWithoutAttr($documentID);
            $this->convertData($documents);
        }
    }
}
