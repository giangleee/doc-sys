<?php

use Illuminate\Database\Seeder;

use App\Services\FileService;
use App\Repositories\DocumentRepository;
use App\Repositories\ServiceUserRepository;
use App\Repositories\StoreRepository;
use App\Repositories\DocumentTypeRepository;
use App\Repositories\FileSetPermissionRepository;
use App\Repositories\FileRepository;
use App\Repositories\DocumentPermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\PositionRepository;
use App\Repositories\DocumentAttributeRepository;
use App\Repositories\FileHistoryRepository;
use App\Repositories\FolderRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\DocumentObjectRepository;
use App\Repositories\FileSetManagementRepository;
use App\Models\Role;
use App\Models\Attribute;
use App\Models\DocumentType;
use App\Models\Folder;

use App\Helper\Constant;
use Carbon\Carbon;

class TransferOldDataToNewSystemV2Seeder extends Seeder
{
    protected $positionOffice = 5;
    protected $positionDocumentName = 7;
    protected $positionFilename = 8;
    protected $path = 'old/version1';
    protected $documentObjectType = '負担割合証';
    protected $docSUStore = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //init
        $fileService = new FileService();
        $documentRepo = new DocumentRepository();
        $serviceUserRepo = new ServiceUserRepository();
        $storeRepo = new StoreRepository();
        $documentTypeRepo = new DocumentTypeRepository();
        $filesetPermissionRepo = new FileSetPermissionRepository();
        $filesetManageRepo = new FileSetManagementRepository();
        $fileRepo = new FileRepository();
        $documentPermissionRepo = new DocumentPermissionRepository();
        $roleRepo = new RoleRepository();
        $positionRepo = new PositionRepository();
        $documentAttrRepo = new DocumentAttributeRepository();
        $fileHistoryRepo = new FileHistoryRepository();
        $folderRepo = new FolderRepository();
        $mailDocumentRepo = new MailDocumentRepository();
        $documentObjectRepo = new DocumentObjectRepository();

        $constantFileFormat = Config::get('constants.file_format');
        $roles = $roleRepo->getAllRole();
        $positionStaff = $positionRepo->filter([['role_id', '=', $roles[Role::STAFF]]])->pluck('id')->toArray();
        $attributeID = Attribute::where('code', Attribute::VALIDITY_PERIOD)->first()->id;
        $documentTypes = $documentTypeRepo->getAll()->pluck('id', 'code');
        $stores = $storeRepo->getHiragicode();
        $dataCheckDuplicate = \Storage::disk('local')->get('check_duplicate.txt');
        $dataCheckDuplicate = array_filter(explode("\n", $dataCheckDuplicate));

        $files = $fileService->getAllFile($this->path);
        foreach ($files as $key => $item) {
            if (strpos($item, $this->documentObjectType) === false) {
                unset($files[$key]);
            }
        }

        //handel data
        foreach ($files as $item) {
            if (in_array($item, $dataCheckDuplicate)) {
                \Log::error('File is duplicate in system: "' . $item . '"');
                continue;
            }

            $file = explode('/', $item);
            $fileName = $file[$this->positionFilename];

            //get extension file
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            //get file name without extension
            $version = 1;
            $realFileName = multibyteTrim(substr($file[$this->positionFilename], 0, strrpos($file[$this->positionFilename], ".")));
            $lastCharacter = mb_substr($realFileName, -1);
            $lastThreeCharacter = mb_substr($realFileName, -3);
            $lastFiveCharacter = mb_substr($realFileName, -5);

            if (preg_match('/^\(.*[0-9]\)$/', $lastThreeCharacter)) {
                $version = (int)str_replace(['(', ')'], '', $lastThreeCharacter);
                $realFileName = mb_substr($realFileName, 0, -3);
            } elseif ($lastThreeCharacter == '(保)') {
                $realFileName = mb_substr($realFileName, 0, -3);
            }

            if ($lastFiveCharacter == '介護保険証') {
                $realFileName = mb_substr($realFileName, 0, -5);
            }

            if ($lastCharacter == 'ケ') {
                $realFileName = mb_substr($realFileName, 0, -1);
            }
            // } elseif (in_array($lastCharacter, array_keys(Constant::NUMBER_JAPAN))) {
            //     $version = Constant::NUMBER_JAPAN[$lastCharacter];
            //     $realFileName = mb_substr($realFileName, 0, -1);
            // } elseif (in_array($lastCharacter, array_values(Constant::NUMBER_JAPAN))) {
            //     $version = (int)$lastCharacter;
            //     $realFileName = mb_substr($realFileName, 0, -1);
            // }

            //get file name
            $fileName = str_replace(['＿', '_', '‗', '₋', '__'], '_', multibyteTrim($realFileName));
            $fileName = explode('_', $fileName);

            //convert document name
            $documentName = $file[$this->positionDocumentName];
            if (isset(Constant::TRANSFER_VERSION_1[$documentName]) && Constant::TRANSFER_VERSION_1[$documentName]) {
                $documentName = Constant::TRANSFER_VERSION_1[$documentName];
            }

            //get store with document name
            $store = str_replace(['＿', '_', '‗', '₋'], '_', $documentName);
            $store = explode('_', $store);
            $store = $store[count($store) - 1] ?? '';
            $documentObject = $documentObjectRepo->getObjectByName($this->documentObjectType);

            //get storeID
            $storeID = (isset($stores[$store]) && $stores[$store]) ? $stores[$store] : false;
            if ($storeID === false) {
                \Log::error('File not found store in system: "' . $item . '"');
                continue;
            }
            if (count($fileName) < 3) {
                \Log::error('File wrong format: "' . $item . '"');
                continue;
            }

            //insert or update service user
            $positionCodeUserService = 1;
            $positionAttribute = 2;
            $nameUserService = $fileName[0];
            if (count($fileName) > 4) {
                $positionAttribute = count($fileName) - 1;
                $positionCodeUserService = count($fileName) - 2;
                for ($i = 1; $i <= count($fileName) - 3; $i++) {
                    $nameUserService .= '_' . $fileName[$i];
                }
            }
            if (is_numeric(convertToHalfwidth($fileName[$positionCodeUserService])) == false) {
                \Log::error('File wrong format code user service: "' . $item . '"');
                continue;
            }

            //insert user service
            $userService = $serviceUserRepo->updateOrCreateData(
                [
                    'code' => convertToHalfwidth($fileName[$positionCodeUserService])
                ],
                [
                    'code' => convertToHalfwidth($fileName[$positionCodeUserService]),
                    'name' => $nameUserService,
                    'user_created' => 1
                ]
            );

            //get attribute document
            try {
                $fileName[$positionAttribute] = convertToHalfwidth($fileName[$positionAttribute]);
                $fileName[$positionAttribute] = str_replace(['~', '～', '～'], '-----', $fileName[$positionAttribute]);
                $datetime = explode('-----', $fileName[$positionAttribute]);

                if (isset($datetime[0]) && $datetime[0] && (int)$datetime[0] <= 0) {
                    \Log::error('File wrong format date: "' . $item. '"');
                    continue;
                }
                if (isset($datetime[1]) && $datetime[1] && (int)$datetime[1] <= 0) {
                    \Log::error('File wrong format date: "' . $item. '"');
                    continue;
                }

                $startDate = (isset($datetime[0]) && $datetime[0]) ? Carbon::parse($datetime[0])->format('Y-m-d') : null;
                $endDate = (isset($datetime[1]) && $datetime[1]) ? Carbon::parse($datetime[1])->format('Y-m-d') : null;
            } catch (\Exception $e) {
                \Log::error('File wrong format date: "' . $item. '"');
                continue;
            }

            //check duplicate document when run multi
            \Storage::disk('local')->append('check_duplicate.txt', $item);

            //folder
            $isCommon = in_array($documentObject->code, DocumentType::DOCUMENT_COMMON[DocumentType::HOME_CARE]) ? 1 : 0;
            $folderStore = $folderRepo->filterFirst([
                'store_id' => $storeID,
                'is_system' => Folder::IS_SYSTEM,
                'service_user_id' => null
            ]);
            //folder service user
            // try {
                $folderServiceUser = $folderRepo->firstOrCreateData(
                    [
                        'service_user_id' => $userService->id,
                        'parent_id' => $folderStore->id,
                        'is_system' => Folder::IS_SYSTEM
                    ],
                    [
                        'service_user_id' => $userService->id,
                        'store_id' => $storeID,
                        'parent_id' => $folderStore->id,
                        'is_system' => Folder::IS_SYSTEM,
                        'name' => $userService->code . 'ー' . $userService->name,
                        'owner_id' => 1,
                    ]
                );
            // } catch (\Exception $e) {
            //     \Log::info($storeID);
            //     \Log::info($folderStore);
            //     return false;
            // }
            
            //folder common
            if ($isCommon) {
                //add or update folder common
                $folder = $folderRepo->firstOrCreateData(
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
                $documentTypeInfo = $documentTypeRepo->find($documentTypes[DocumentType::HOME_CARE]);
                $storeInfo = $storeRepo->find($storeID);
                $folder = $folderRepo->firstOrCreateData(
                    [
                        'parent_id' => $folderServiceUser->id,
                        'document_type_id' => $documentTypes[DocumentType::HOME_CARE]
                    ],
                    [
                        'parent_id' => $folderServiceUser->id,
                        'document_type_id' => $documentTypes[DocumentType::HOME_CARE],
                        'is_system' => Folder::IS_SYSTEM,
                        'name' => $documentTypeInfo->name . '(' . $storeInfo->name . ')',
                        'owner_id' => 1,
                    ]
                );
            }

            //check document exist
            if (isset($this->docSUStore[$userService->id . '_' . $storeID])) {
                //get info document
                $documentInfo = $documentRepo->find($this->docSUStore[$userService->id . '_' . $storeID]);
                //update document attribute
                if ($startDate != null && $endDate != null) {
                    $documentInfoAttr = $documentAttrRepo->getValuePeriod($documentInfo->id);
                    $startDateAttr = $startDate > $endDate ? $endDate : $startDate;
                    $endDateAttr = $startDate > $endDate ? $startDate : $endDate;
                    if (!$documentInfoAttr->start_date || $startDateAttr > $documentInfoAttr->start_date) {
                        $documentInfoAttr->start_date = $startDateAttr;
                    }
                    if (!$documentInfoAttr->end_date || $endDateAttr > $documentInfoAttr->end_date) {
                        $documentInfoAttr->end_date = $endDateAttr;
                    }
                    $documentInfoAttr->save();
                }
            } else {
                //create document
                $dataDocument = [
                    'store_id' => $storeID,
                    'owner_id' => 1,
                    'document_type_id' => $documentTypes[DocumentType::HOME_CARE],
                    'document_object_id' => $documentObject->id,
                    'service_user_id' => $userService->id,
                    'name' => $documentName,
                    'folder_id' => $folder->id
                ];
                $documentInfo = $documentRepo->create($dataDocument);
                $this->docSUStore[$userService->id . '_' . $storeID] = $documentInfo->id;
                //insert mail document
                $mailDocumentRepo->saveMailDocument($documentInfo);
                //create attibute in document
                if (isset($fileName[$positionAttribute]) && $fileName[$positionAttribute]) {
                    $dataDocumentAtt = [
                        'attribute_id' => $attributeID,
                        'document_id' => $documentInfo->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    if ($startDate != null && $endDate != null && $startDate > $endDate) {
                        $dataDocumentAtt['start_date'] = $endDate;
                        $dataDocumentAtt['end_date'] = $startDate;
                    }
                    $documentAttrRepo->create($dataDocumentAtt);
                }
                //firstorcreate fileset management
                $dataFilesetManage = [
                    'service_user_id' => $userService->id,
                    'store_id' => $storeID,
                    'document_type_id' => $documentTypes[DocumentType::HOME_CARE]
                ];
                $filesetManage = $filesetManageRepo->firstOrCreateData($dataFilesetManage, $dataFilesetManage);
                //firstorcreate fileset permission
                $filesetPermission = $filesetPermissionRepo->firstOrCreateData(
                    [
                        'service_user_id' => $userService->id,
                        'file_set_management_id' => $filesetManage->id,
                        'store_id' => $storeID
                    ],
                    [
                        'file_set_management_id' => $filesetManage->id,
                        'service_user_id' => $userService->id,
                        'store_id' => $storeID,
                        'positions_id' => implode(',', $positionStaff)
                    ]
                );
                //add permission for document
                $documentPermissionRepo->create([
                    'document_id' => $documentInfo->id,
                    'store_id' => $storeID,
                    'positions_id' => implode(',', $positionStaff)
                ]);
            }

            //file and file history
            $fileInfo = $fileRepo->findByFilenameWithDocument($documentInfo->id, $item);
            if (!$fileInfo) {
                $fileInfo = $fileRepo->create([
                    'document_id' => $documentInfo->id,
                    'file_format' => $constantFileFormat[$extension],
                    'original_name' => $file[$this->positionFilename],
                    'url' => $item,
                    'size' => \Storage::size($item),
                    'version' => $version
                    ]);
            } else {
                if ($fileInfo->version < $version) {
                    //update file
                    $fileInfo->file_format = $constantFileFormat[$extension];
                    $fileInfo->original_name = $file[$this->positionFilename];
                    $fileInfo->url = $item;
                    $fileInfo->size = \Storage::size($item);
                    $fileInfo->version = $version;
                }
            }

            //insert file history
            $fileHistoryRepo->create([
                'user_id' => 1,
                'file_id' => $fileInfo->id,
                'file_format' => $constantFileFormat[$extension],
                'original_name' => $file[$this->positionFilename],
                'url' => $item,
                'size' => \Storage::size($item),
                'version' => $version
            ]);

        }

        \Log::info('insert sucess v2');
    }
}