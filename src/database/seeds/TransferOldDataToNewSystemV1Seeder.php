<?php

use Illuminate\Database\Seeder;

use App\Services\FileService;
use App\Repositories\DocumentRepository;
use App\Repositories\ServiceUserRepository;
use App\Repositories\OfficeRepository;
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
use App\Models\Role;
use App\Models\Attribute;

use App\Helper\Constant;
use Carbon\Carbon;

class TransferOldDataToNewSystemV1Seeder extends Seeder
{
    protected $positionOffice = 5;
    protected $positionDocumentName = 7;
    protected $positionFilename = 8;
    protected $path = 'old/version1';
    protected $documentObjectType = '負担割合証';

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
        $officeRepo = new OfficeRepository();
        $documentTypeRepo = new DocumentTypeRepository();
        $filesetPermissionRepo = new FileSetPermissionRepository();
        $fileRepo = new FileRepository();
        $documentPermissionRepo = new DocumentPermissionRepository();
        $roleRepo = new RoleRepository();
        $positionRepo = new PositionRepository();
        $documentAttrRepo = new DocumentAttributeRepository();
        $fileHistoryRepo = new FileHistoryRepository();
        $folderRepo = new FolderRepository();
        $mailDocumentRepo = new MailDocumentRepository();
        $documentObjectRepo = new DocumentObjectRepository();

        //get data
        $documentAttrs = $permissions = $fileDocument = $permissionFileset = $serviceUserDocument = [];
        $constantFileFormat = Config::get('constants.file_format');
        $userServices = $serviceUserRepo->getAll()->pluck('code')->toArray();
        $offices = $officeRepo->getAll()->pluck('id', 'code')->toArray();
        $documentTypes = $documentTypeRepo->getAll()->pluck('id', 'code');
        $roles = $roleRepo->getAllRole();
        $positionStaff = $positionRepo->filter([['role_id', '=', $roles[Role::STAFF]]])->pluck('id')->toArray();
        $attributeID = Attribute::where('code', Attribute::VALIDITY_PERIOD)->first()->id;
        $files = $fileService->getAllFile($this->path);
        $dataCheckDuplicate = \Storage::disk('local')->get('check_duplicate.txt');
        $dataCheckDuplicate = array_filter(explode("\n", $dataCheckDuplicate));

        //convert hiragicode to office code
        $hiragicodeWithOffice = convertHiragicodeToOffice();

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
            } elseif (in_array($lastCharacter, array_keys(Constant::NUMBER_JAPAN))) {
                $version = Constant::NUMBER_JAPAN[$lastCharacter];
                $realFileName = mb_substr($realFileName, 0, -1);
            } elseif (in_array($lastCharacter, array_values(Constant::NUMBER_JAPAN))) {
                $version = (int)$lastCharacter;
                $realFileName = mb_substr($realFileName, 0, -1);
            }
            
            //get file name
            $fileName = str_replace(['＿', '_', '‗', '₋', '__'], '_', multibyteTrim($realFileName));
            $fileName = explode('_', $fileName);

            //convert document name
            $documentName = $file[$this->positionDocumentName];
            if (isset(Constant::TRANSFER_VERSION_1[$documentName]) && Constant::TRANSFER_VERSION_1[$documentName]) {
                $documentName = Constant::TRANSFER_VERSION_1[$documentName];
            } 

            //get office with document name
            $office = str_replace(['＿', '_', '‗', '₋'], '_', $documentName);
            $office = explode('_', $office);
            $documentObjectName = $office[0];
            $office = $hiragicodeWithOffice[$office[count($office) - 1]] ?? '';
            $documentObject = $documentObjectRepo->getObjectByName($documentObjectName);

            //get officeID
            $officeID = (isset($offices[$office]) && $offices[$office]) ? $offices[$office] : false;
            if ($officeID === false) {
                \Log::error('File not found office in system: "' . $item . '"');
                continue;
            }
            if (count($fileName) < 3) {
                \Log::error('File wrong format: "' . $item . '"');
                continue;
            }

            //get folder office
            $folderOffice = $folderRepo->getFolderByOfficeID($officeID);

            $positionCodeUserService = 1;
            $positionAttribute = 2;
            $nameUserService = $fileName[0];
            if (count($fileName) > 4) {
                $positionAttribute = count($fileName) - 1;
                $positionCodeUserService = count($fileName) - 2;
                for ($i = 1; $i <= count($fileName) - 3; $i++) {
                    $nameUserService .= '＿' . $fileName[$i];
                }
            }
            if (is_numeric(convertToHalfwidth($fileName[$positionCodeUserService])) == false) {
                \Log::error('File wrong format code user service: "' . $item . '"');
                continue;
            }

            //insert user service
            $userService = $serviceUserRepo->updateOrCreateData(
                ['code' => convertToHalfwidth($fileName[$positionCodeUserService])],
                [
                    'code' => convertToHalfwidth($fileName[$positionCodeUserService]),
                    'name' => $nameUserService,
                    'office_id' => $officeID,
                    'user_created' => 1
                    ]
            );

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

            $filesetPermission = $filesetPermissionRepo->filter([['service_user_id', '=', $userService->id]])
                ->pluck('positions_id', 'office_id')
                ->toArray();
           
            //build data document
            $dataDocument = [
                'office_id' => $officeID,
                'owner_id' => 1,
                'document_type_id' => $documentTypes['0001'],
                'document_object_id' => $documentObject->id,
                'service_user_id' => $userService->id,
                'name' => $documentName,
                'folder_id' => $folderOffice->id
            ];

            //create attibute in document
            $dataDocumentAtt = [];
            if (isset($fileName[$positionAttribute]) && $fileName[$positionAttribute]) {
                $dataDocumentAtt = [
                    'attribute_id' => $attributeID,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'user_service_id' => $userService->id,
                ];
                if ($startDate != null && $endDate != null && $startDate > $endDate) {
                    $dataDocumentAtt['start_date'] = $endDate;
                    $dataDocumentAtt['end_date'] = $startDate;
                }
            }


            //create file in document
            if ($version > 1) {
                $fileDocument['update'][] = [
                    'file_format' => $constantFileFormat[$extension],
                    'original_name' => $file[$this->positionFilename],
                    'url' => $item,
                    'size' => \Storage::size($item),
                    'version' => $version,
                    'real_filename' => $realFileName . '.' . $extension,
                    'document_info' => $realFileName,
                    'add_info_document' => [
                        'document' => $dataDocument,
                        'document_attr' => $dataDocumentAtt,
                        'permission' => $filesetPermission,
                        'user_service_id' => $userService->id,
                        'office_id' => $officeID
                    ]
                ];
            } else {
                $documentID = null;
                \Storage::disk('local')->append('check_duplicate.txt', $item);
                if (isset($serviceUserDocument[$userService->id]['document_id'])) {
                    $documentID = $serviceUserDocument[$userService->id]['document_id'];
                } else {
                    //insert document
                    $document = $documentRepo->create($dataDocument);
                    $documentID = $document->id;
                    $serviceUserDocument[$userService->id]['document_id'] = $documentID;
                    //insert mail document
                    $mailDocumentRepo->saveMailDocument($document);

                    //create attibute in document
                    //TODO update with new date
                    if (isset($fileName[$positionAttribute]) && $fileName[$positionAttribute]) {
                        $dataDocumentAtt['document_id'] = $documentID;
                        $documentAttrs[] = $dataDocumentAtt;
                        $serviceUserDocument[$userService->id]['start_date'] = $dataDocumentAtt['start_date'];
                        $serviceUserDocument[$userService->id]['end_date'] = $dataDocumentAtt['end_date'];
                    }

                    //create document permission
                    if (!empty($filesetPermission)) {
                        foreach ($filesetPermission as $k => $v) {
                            $permissions[] = [
                                'document_id' => $documentID,
                                'office_id' => $k,
                                'positions_id' => $v,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                        }
                    } else {
                        $permissions[] = [
                            'document_id' => $documentID,
                            'office_id' => $officeID,
                            'positions_id' => implode(',', $positionStaff),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        $permissionFileset[] = [
                            'service_user_id' => $userService->id,
                            'office_id' => $officeID,
                            'positions_id' => implode(',', $positionStaff),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                    }
                }

                //insert file
                $fileDocument['insert'][] = [
                    'document_id' => $documentID,
                    'file_format' => $constantFileFormat[$extension],
                    'original_name' => $file[$this->positionFilename],
                    'url' => $item,
                    'size' => \Storage::size($item),
                    'version' => $version,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }

        //insert data
        if (isset($fileDocument['insert']) && !empty($fileDocument['insert'])) {
            foreach ($fileDocument['insert'] as $item) {
                $fileDocumentInsert = $fileRepo->create($item);
                $fileHistoryInsert = [
                    'user_id' => 1,
                    'file_id' => $fileDocumentInsert->id,
                    'file_format' => $fileDocumentInsert->file_format,
                    'original_name' => $fileDocumentInsert->original_name,
                    'url' => $fileDocumentInsert->url,
                    'size' => $fileDocumentInsert->size,
                    'version' => $fileDocumentInsert->version
                ];
                $fileHistoryRepo->create($fileHistoryInsert);
            }
        }
        if (!empty($permissions)) {
            $permissionChunk = array_chunk($permissions, 2000);
            foreach ($permissionChunk as $item) {
                $documentPermissionRepo->insertMany($item);
            }
        }
        if (!empty($permissionFileset)) {
            $permissionFilesetChunk = array_chunk($permissionFileset, 2000);
            foreach ($permissionFilesetChunk as $item) {
                $filesetPermissionRepo->insertMany($item);
            }
        }

        if (isset($fileDocument['update']) && !empty($fileDocument['update'])) {
            foreach ($fileDocument['update'] as $item) {
                $documentInfo = str_replace($item['original_name'], $item['document_info'], $item['url']);
                $documentFile = $fileRepo->findByFilename($documentInfo);
                if (!$documentFile) {
                    $documentID = null;
                    \Storage::disk('local')->append('check_duplicate.txt', $item['url']);
                    if (isset($serviceUserDocument[$item['add_info_document']['user_service_id']]['document_id'])) {
                        $documentID = $serviceUserDocument[$item['add_info_document']['user_service_id']]['document_id'];
                    } else {
                        //add document
                        $document = $documentRepo->create($item['add_info_document']['document']);
                        $documentID = $document->id;
                        $serviceUserDocument[$item['add_info_document']['user_service_id']]['document_id'] = $documentID;
                        //insert mail document
                        $mailDocumentRepo->saveMailDocument($document);

                        //create attibute in document
                        if ($item['add_info_document']['document_attr']) {
                            $item['add_info_document']['document_attr']['document_id'] = $documentID;
                            $item['add_info_document']['document_attr']['user_service_id'] = $item['add_info_document']['user_service_id'];
                            $documentAttrs[] = $item['add_info_document']['document_attr'];
                            // $documentAttrRepo->create($item['add_info_document']['document_attr']);
                            $serviceUserDocument[$item['add_info_document']['user_service_id']]['start_date'] = $dataDocumentAtt['start_date'];
                            $serviceUserDocument[$item['add_info_document']['user_service_id']]['end_date'] = $dataDocumentAtt['end_date'];
                        }

                        //create permission
                        if ($item['add_info_document']['permission'] && !empty($item['add_info_document']['permission'])) {
                            $permissionsData = [];
                            foreach ($item['add_info_document']['permission'] as $k => $v) {
                                $permissionsData[] = [
                                    'document_id' => $documentID,
                                    'office_id' => $k,
                                    'positions_id' => $v,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ];
                            }
                            $documentPermissionRepo->insertMany($permissionsData);
                        } else {
                            $permissionsData = [
                                'document_id' => $documentID,
                                'office_id' => $item['add_info_document']['office_id'],
                                'positions_id' => implode(',', $positionStaff),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                            $permissionFilesetData = [
                                'service_user_id' => $item['add_info_document']['user_service_id'],
                                'office_id' => $item['add_info_document']['office_id'],
                                'positions_id' => implode(',', $positionStaff),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                            $documentPermissionRepo->create($permissionsData);
                            $filesetPermissionRepo->create($permissionFilesetData);
                        }
                    }
                    
                    //insert file
                    $fileData = [
                        'document_id' => $documentID,
                        'file_format' => $item['file_format'],
                        'original_name' => $item['original_name'],
                        'url' => $item['url'],
                        'size' => $item['size'],
                        'version' => $item['version'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    $fileDocumentInsert = $fileRepo->create($fileData);
                    //get document file
                    $documentFile = $fileRepo->findByFilename($documentInfo);
                }

                if (!$documentFile) {
                    \Log::error('File wrong not found version: "' . $item['url']. '"');
                    continue;
                }

                if ($documentFile->version < $item['version']) {
                    //update data
                    $documentFile->file_format = $item['file_format'];
                    $documentFile->original_name = $item['original_name'];
                    $documentFile->url = $item['url'];
                    $documentFile->size = $item['size'];
                    $documentFile->version = $item['version'];
                    $documentFile->save();

                    //insert history with current version
                    $dataFileHistory = [
                        'user_id' => 1,
                        'file_id' => $documentFile->id,
                        'file_format' => $documentFile->file_format,
                        'original_name' => $documentFile->original_name,
                        'url' => $documentFile->url,
                        'size' => $documentFile->size,
                        'version' => $documentFile->version
                    ];
                    $fileHistoryRepo->create($dataFileHistory);
                }  else {
					//insert history with current version
                    $dataFileHistory = [
                        'user_id' => 1,
                        'file_id' => $documentFile->id,
                        'file_format' => $item['file_format'],
                        'original_name' => $item['original_name'],
                        'url' => $item['url'],
                        'size' => $item['size'],
                        'version' => $item['version']
                    ];
                    $fileHistoryRepo->create($dataFileHistory);
                }
                \Storage::disk('local')->append('check_duplicate.txt', $item['url']);
            }
        }

        if (!empty($documentAttrs)) {
            foreach ($documentAttrs as $key => $item) {
                if ($item['end_date'] < $serviceUserDocument[$item['user_service_id']]['end_date']) {
                    $documentAttrs[$key]['start_date'] = $serviceUserDocument[$item['user_service_id']]['start_date'];
                    $documentAttrs[$key]['end_date'] = $serviceUserDocument[$item['user_service_id']]['end_date'];
                }
                unset($documentAttrs[$key]['user_service_id']);
            }
            $documentAttrChunk = array_chunk($documentAttrs, 2000);
            foreach ($documentAttrChunk as $item) {
                $documentAttrRepo->insertMany($item);
            }
        }
        
        \Log::info('insert sucess');
    }
}
