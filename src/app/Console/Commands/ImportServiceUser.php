<?php

namespace App\Console\Commands;

use App\Repositories\DocumentRepository;
use App\Repositories\FileRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\StoreRepository;
use Illuminate\Console\Command;
use App\Services\FileService;
use App\Repositories\OfficeRepository;
use App\Repositories\ServiceUserRepository;
use App\Repositories\FileSetManagementRepository;
use App\Repositories\FileSetPermissionRepository;
use App\Repositories\DocumentTypeRepository;
use App\Repositories\PositionRepository;
use App\Repositories\FolderRepository;
// use App\Repositories\UserRepository;
use App\Repositories\MailTemplateRepository;
use Carbon\Carbon;
use App\Helper\Constant;
use Illuminate\Support\Facades\Validator;
use App\Models\Folder;
use App\Models\MailTemplate;
use App\Mail\DocumentMailAlert;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErrorExport;
use App\Imports\OfficeImport;

class ImportServiceUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:serviceuser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import service user';
    protected $errorStore = [];
    protected $errorServiceUser = [];
    protected $serviceUserCodes = [];

    const COL_SERVICE_USER = [
        'service_user_name' => 1,
        'document_type' => 2,
        'contract_cancel_date' => 5,
        'service_user_id' => 19
    ];
    const COL_OFFICE = [
        'hiragi_code' => 0,
        'office_name_simple' => 3,
        'status_code' => 4
    ];
    const CUSTOM_VALIDATE_SERVICE_USER = [
        self::COL_SERVICE_USER['service_user_name'] => '利用者',
        self::COL_SERVICE_USER['document_type'] => '契約事業所',
        self::COL_SERVICE_USER['contract_cancel_date'] => '廃止日',
        self::COL_SERVICE_USER['service_user_id'] => '利用者ID',
    ];
    const CUSTOM_VALIDATE_OFFICE = [
        self::COL_OFFICE['hiragi_code'] => '事業所コード',
        self::COL_OFFICE['office_name_simple'] => '事業所名',
        self::COL_OFFICE['status_code'] => '廃止フラグ（1は廃止事業所）',
    ];
    const OFFICE_STOP = 1;

    private function buildValidation($type, $totalColumn, $hiragicode = null)
    {
        $validations = [];
        for ($i = 0; $i < $totalColumn; $i++) {
            if ($type == 'store' && in_array($i, array_values(self::COL_OFFICE))) {
                $validations[$i] = 'required';
                if ($i == self::COL_OFFICE['hiragi_code']) {
                    $validations[$i] .= '|in:' . implode(',', array_keys($hiragicode));
                }
            } elseif ($type == 'service_user' && in_array($i, array_values(self::COL_SERVICE_USER))) {
                $validations[$i] = 'required';
                if ($i == self::COL_SERVICE_USER['service_user_name']) {
                    $validations[$i] .= '|max:50';
                }
                if ($i == self::COL_SERVICE_USER['service_user_id']) {
                    $validations[$i] .= '|max:20|regex:/^[^<>"\']+$/';
                }
            } else {
                $validations[$i] = 'nullable';
            }
        }

        return $validations;
    }

    private function validationServiceUser($content, $dataValidator)
    {
        $data = [];
        $dateOld = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
        foreach ($content as $key => $item) {
            $validator = Validator::make($item, $dataValidator, [
                'required' => __('message.service_user.import_data_empty')
            ], self::CUSTOM_VALIDATE_SERVICE_USER);

            if ($validator->fails()) {
                $this->errorServiceUser[$key + 2] = $validator->messages()->get('*');
                unset($content[$key]);
                continue;
            }

            if ($item[self::COL_SERVICE_USER['contract_cancel_date']]) {
                try {
                    $dateEndContract = Carbon::parse($item[self::COL_SERVICE_USER['contract_cancel_date']])
                        ->format('Y-m-d');
                    if ($dateEndContract < $dateOld) {
                        $data['contract_cancelled'][$item[self::COL_SERVICE_USER['service_user_id']]][] = [
                            'service_user_name' => $item[self::COL_SERVICE_USER['service_user_name']],
                            'document_type' => $item[self::COL_SERVICE_USER['document_type']],
                            'contract_cancel_date' => $item[self::COL_SERVICE_USER['contract_cancel_date']]
                        ];
                    }
                } catch (\Exception $e) {
                    $this->errorServiceUser[$key + 2][self::COL_SERVICE_USER['contract_cancel_date']][] = __('message.service_user.import_format_date_error', ['field' => self::CUSTOM_VALIDATE_SERVICE_USER[self::COL_SERVICE_USER['contract_cancel_date']]]);
                    unset($content[$key]);
                    continue;
                }
            }

            if (
                isset($item[self::COL_SERVICE_USER['service_user_id']])
                && !in_array($item[self::COL_SERVICE_USER['service_user_id']], $this->serviceUserCodes)
                ) {
                $this->serviceUserCodes[] = $item[self::COL_SERVICE_USER['service_user_id']];
            }

            $data[$item[self::COL_SERVICE_USER['service_user_id']]][] = [
                'service_user_name' => $item[self::COL_SERVICE_USER['service_user_name']],
                'document_type' => $item[self::COL_SERVICE_USER['document_type']],
                'contract_cancel_date' => $item[self::COL_SERVICE_USER['contract_cancel_date']],
                'line' => $key + 2
            ];
        }

        return $data;
    }

    private function validationOffice($content, $dataValidator)
    {
        $data = [];
        foreach ($content as $key => $item) {
            //remove if office stop
            if (isset($item[4]) && (int)$item[4] == self::OFFICE_STOP) {
                unset($content[$key]);
                continue;
            }

            $validator = Validator::make($item, $dataValidator, [
                'required' => __('message.service_user.import_data_empty')
            ], self::CUSTOM_VALIDATE_OFFICE);

            if ($validator->fails()) {
                $this->errorStore[$key + 2] = $validator->messages()->get('*');
                unset($content[$key]);
                continue;
            }

            $data['store'][$item[self::COL_OFFICE['office_name_simple']]] = $item[self::COL_OFFICE['hiragi_code']];
        }
        return $data;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //init
        $fileService = new FileService();
        $storeRepo = new StoreRepository();
        $serviceUserRepo = new ServiceUserRepository();
        $filesetManagementRepo = new FileSetManagementRepository();
        $filesetPermissionRepo = new FileSetPermissionRepository();
        $documentTypeRepo = new DocumentTypeRepository();
        $positionRepo = new PositionRepository();
        $folderRepo = new FolderRepository();
        // $userRepo = new UserRepository();
        $mailTemplateRepo = new MailTemplateRepository();
        $documentRepo = new DocumentRepository();
        $fileRepo = new FileRepository();
        $mailDocumentRepo = new MailDocumentRepository();

        //get data
        $hiragicodeStore = $storeRepo->getHiragicode('code');
//        $hiragicodeNotUse = hiragicodeNotUse();
        $stores = $storeRepo->getInfoStoreByCode();
        $serviceUsers = $serviceUserRepo->getAllWithTrash();
        $documentTypes = $documentTypeRepo->getInfoGroupByCode();
        $positionIDs = $positionRepo->getAll()->pluck('id')->toArray();

        $path = 'import/' . Carbon::now()->format('Y-m-d');
        $files = $fileService->getAllFile($path);

        //group file import
        $fileImport = [];
        foreach ($files as $item) {
            $infoFile = explode("/", $item);
            $fileNameArr = explode('_', $item);
            $fileImport[$infoFile[2]]['user_import'] = $fileNameArr[1];
            if (strpos($infoFile[3], 'service_') !== false) {
                $fileImport[$infoFile[2]]['service_user'] =  $item;
            }
            if (strpos($infoFile[3], 'office_') !== false) {
                $fileImport[$infoFile[2]]['store'] =  $item;
            }
        }

        //build validation
        $validattionOffice = $this->buildValidation('store', Constant::TOTAL_COLUMN_FILE_OFFICE, $hiragicodeStore);
        $validationServiceUser = $this->buildValidation('service_user', Constant::TOTAL_COLUMN_FILE_SERVICE_USER);

        //get content file import
        foreach ($fileImport as $item) {
            $this->serviceUserCodes = [];
            $this->errorStore = [];
            $this->errorServiceUser = [];
            //convert content to array
            $contentServiceUSer = convertCSVToArray($fileService->getFile($item['service_user']), 1, 'UTF-8', 'SJIS');
            $contentStore = (new OfficeImport)->toArray($item['store'], config('filesystem.default'));
            unset($contentStore[0][0]);
            $contentStore = array_values($contentStore[0]);

            //check validation
            $contentServiceUSer = $this->validationServiceUser($contentServiceUSer, $validationServiceUser);
            $contentStore = $this->validationOffice($contentStore, $validattionOffice);

            $contractCancelled = [];
            if (isset($contentServiceUSer['contract_cancelled'])) {
                $contractCancelled = $contentServiceUSer['contract_cancelled'];
                unset($contentServiceUSer['contract_cancelled']);
            }

            //insert or update user service
            foreach ($contentServiceUSer as $key => $suValue) {
                foreach ($suValue as $value) {
                    //check office isset
                    if (!isset($contentStore['store'][$value['document_type']])
                        || !isset($hiragicodeStore[$contentStore['store'][$value['document_type']]])
                    ) {
                        $this->errorServiceUser[$value['line']][self::COL_SERVICE_USER['document_type']][] = __('message.service_user.import_office_code_error', [
                            'field' => self::CUSTOM_VALIDATE_SERVICE_USER[self::COL_SERVICE_USER['document_type']]
                        ]);
                        //remove SU id when error
                        $keyCodeSU = array_search($key, $this->serviceUserCodes);
                        if ($keyCodeSU) {
                            unset($this->serviceUserCodes[$keyCodeSU]);
                        }
                        continue;
                    }
                    $storeCode = $hiragicodeStore[$contentStore['store'][$value['document_type']]];
                    if (!isset($stores[$storeCode])) {
                        $this->errorServiceUser[$value['line']][self::COL_SERVICE_USER['document_type']][] = __('message.service_user.import_office_code_error', [
                            'field' => self::CUSTOM_VALIDATE_SERVICE_USER[self::COL_SERVICE_USER['document_type']]
                        ]);
                        //remove SU id when error
                        $keyCodeSU = array_search($key, $this->serviceUserCodes);
                        if ($keyCodeSU) {
                            unset($this->serviceUserCodes[$keyCodeSU]);
                        }
                        continue;
                    }

                    //insert or update info service user
                    if (isset($serviceUsers[$key])) {
                        $serviceUserRepo->rollbackAndUpdate(
                            $serviceUsers[$key],
                            ['name' => $value['service_user_name']]
                        );
                        $folderRepo->updateNameFolderServiceUser(
                            $serviceUsers[$key],
                            $key . 'ー' . $value['service_user_name']
                        );
                    } else {
                        $serviceUser = $serviceUserRepo->create([
                            'office_id' => null,
                            'user_created' => $item['user_import'],
                            'code' => $key,
                            'name' => $value['service_user_name'],
                        ]);
                        $serviceUsers[$key] = $serviceUser->id;
                    }

                    //check document exist fileset
                    $documentTypeInfo = null;
                    foreach (Constant::DOCUMENT_TYPE_IMPORT as $textSearch => $documentType) {
                        if (strpos($value['document_type'], $textSearch) !== false) {
                            $documentTypeInfo = $documentTypes[$documentType];
                        }
                    }
                    if (is_null($documentTypeInfo)) {
                        $this->errorServiceUser[$value['line']][self::COL_SERVICE_USER['document_type']][] = __('message.service_user.import_document_type_error', ['field' => self::CUSTOM_VALIDATE_SERVICE_USER[self::COL_SERVICE_USER['document_type']]]);
                        //remove SU id when error
                        $keyCodeSU = array_search($key, $this->serviceUserCodes);
                        if ($keyCodeSU) {
                            unset($this->serviceUserCodes[$keyCodeSU]);
                        }
                        continue;
                    }

                    $filesetManage = $filesetManagementRepo->filterWithTrashed([
                        'store_id' => $stores[$storeCode]['id'],
                        'document_type_id' => $documentTypeInfo['id'],
                        'service_user_id' => $serviceUsers[$key]
                    ]);
                    $contractCancelDate = isset($value['contract_cancel_date'])
                        ? Carbon::parse($value['contract_cancel_date'])->format('Y-m-d')
                        : '';
                    $dateOld = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
                    if ($filesetManage && $contractCancelDate < $dateOld) {
                        $filesetManage->update(['contract_cancel_date' => $contractCancelDate]);
                        $checkAnotherFileSetAvailable = $filesetManagementRepo->getAllFilesetInServiceUser(
                            $serviceUsers[$key],
                            $filesetManage->id
                        );
                        //remove SU id when cancel date over 5 years
                        if (!$checkAnotherFileSetAvailable) {
                            $keyCodeSU = array_search($key, $this->serviceUserCodes);
                            if ($keyCodeSU) {
                                unset($this->serviceUserCodes[$keyCodeSU]);
                            }
                        }
                        continue;
                    }

                    if (!$filesetManage) {
                        //insert fileset management
                        $filesetManage = $filesetManagementRepo->create([
                            'store_id' => $stores[$storeCode]['id'],
                            'document_type_id' => $documentTypeInfo['id'],
                            'service_user_id' => $serviceUsers[$key],
                            'contract_cancel_date' => $contractCancelDate
                        ]);

                        //insert fileset permission
                        $filesetPermissionRepo->create([
                            'file_set_management_id' => $filesetManage->id,
                            'store_id' => $stores[$storeCode]['id'],
                            'positions_id' => implode(',', $positionIDs)
                        ]);

                    } else {
                        $filesetManage->update(['contract_cancel_date' => $contractCancelDate]);
                    }

                    //get folder store
                    $folderStore = $folderRepo->getFolderStore($stores[$storeCode]['id']);
                    //check exist folder service user
                    $folderServiceUser = $folderRepo->filterFirst([
                        'service_user_id' => $serviceUsers[$key],
                        'parent_id' => $folderStore->id,
                        'is_system' => Folder::IS_SYSTEM
                    ]);
                    if (!$folderServiceUser) {
                        //create folder service user
                        $folderServiceUser = $folderRepo->create([
                            'service_user_id' => $serviceUsers[$key],
                            'store_id' => $stores[$storeCode]['id'],
                            'owner_id' => $item['user_import'],
                            'parent_id' => $folderStore->id,
                            'is_system' => Folder::IS_SYSTEM,
                            'name' => $key . 'ー' . $value['service_user_name']
                        ]);

                        //create folder document type
                        $folderRepo->create([
                            'owner_id' => $item['user_import'],
                            'parent_id' => $folderServiceUser->id,
                            'is_system' => Folder::IS_SYSTEM,
                            'name' => $documentTypeInfo['name'] . '（' . $stores[$storeCode]['name'] . '）',
                            'document_type_id' => $documentTypeInfo['id']
                        ]);
                    } else {
                        //update name folder
                        $folderServiceUser->update([
                            'name' => $key . 'ー' . $value['service_user_name']
                        ]);
                        //get folder document type
                        $folderDocumentType = $folderRepo->filterFirst([
                            'parent_id' => $folderServiceUser->id,
                            'document_type_id' => $documentTypeInfo['id']
                        ]);
                        if (!$folderDocumentType) {
                            //create folder document type
                            $folderRepo->create([
                                'owner_id' => $item['user_import'],
                                'parent_id' => $folderServiceUser->id,
                                'is_system' => Folder::IS_SYSTEM,
                                'name' => $documentTypeInfo['name'] . '（' . $stores[$storeCode]['name'] . '）',
                                'document_type_id' => $documentTypeInfo['id']
                            ]);
                        }
                    }
                }
            }

            //delete service user
            if (!empty($this->serviceUserCodes)) {
                //delete all
                $serviceUserRepo->deleteAll();

                //restore user if exist
                $chunkServiceUser = array_chunk($this->serviceUserCodes, 5000);
                foreach ($chunkServiceUser as $itemChunk) {
                    $serviceUserRepo->restoreByCode($itemChunk);
                }

                $serviceUserDeleted = array_diff_key($serviceUsers, array_flip($this->serviceUserCodes));
                $serviceUsersInfo = $serviceUserRepo->getServiceUsersByCodes(array_keys($serviceUserDeleted), true);
                foreach ($serviceUsersInfo as $serviceUserInfo) {
                    $serviceUserInfo->folder()->delete();
                    $fileSetManagements = $serviceUserInfo->fileSetManagements();
                    foreach ($fileSetManagements->get() as $fileSetManagement) {
                        $fileSetManagement->fileSetPermission()->delete();
                    }
                    $fileSetManagements->delete();
                    $documents = $serviceUserInfo->documents();
                    foreach ($documents->get() as $document) {
                        $document->files()->delete();
                        $document->mailDocument()->delete();
                        $document->delete();
                    }
                }
            }

            //delete cancelled fileset
            foreach ($contractCancelled as $key => $suValue) {
                foreach ($suValue as $value) {
                    if (isset($hiragicodeStore[$contentStore['store'][$value['document_type']]])) {
                        $storeCode = $hiragicodeStore[$contentStore['store'][$value['document_type']]];
                        $documentTypeInfo = null;
                        foreach (Constant::DOCUMENT_TYPE_IMPORT as $textSearch => $documentType) {
                            if (strpos($value['document_type'], $textSearch) !== false) {
                                $documentTypeInfo = $documentTypes[$documentType];
                            }
                        }
                        if (isset($stores[$storeCode]) && !empty($documentTypeInfo)) {
                            // delete folder of cancelled fileset
                            $folder = $folderRepo->findFolderFileSet(
                                $serviceUsers[$key],
                                $documentTypeInfo['id'],
                                $stores[$storeCode]['id']
                            );
                            $folderParentId = null;
                            if ($folder) {
                                $folderParentId = $folder->parent_id;
                                $folder->delete();
                            }

                            //delete fileset
                            $filesetManage = $filesetManagementRepo->filterFirst([
                                'store_id' => $stores[$storeCode]['id'],
                                'document_type_id' => $documentTypeInfo['id'],
                                'service_user_id' => $serviceUsers[$key]
                            ]);
                            if ($filesetManage) {
                                $checkAnotherFileSetAvailable = $filesetManagementRepo->checkAnotherFileSetAvailable(
                                    $serviceUsers[$key],
                                    $documentTypeInfo['id'],
                                    $stores[$storeCode]['id'],
                                    $filesetManage->id
                                );
                                if (!$checkAnotherFileSetAvailable) {
                                    $folderRepo->delete($folderParentId);
                                }
                                $filesetManage->fileSetPermission()->delete();
                                $filesetManage->delete();
                            }
                            $documentsInFileSet = $documentRepo->getDocumentsInFileSet(
                                $serviceUsers[$key],
                                $documentTypeInfo['id'],
                                $stores[$storeCode]['id']
                            );
                            $docIdsToDelete = $documentsInFileSet->pluck('id')->toArray();
                            // delete all document in fileset
                            $documentRepo->deleteByField('id', $docIdsToDelete);

                            //delete all file belong to document
                            $fileRepo->deleteByField('document_id', $docIdsToDelete);

                            // delete all mail document
                            $mailDocumentRepo->deleteByField('document_id', $docIdsToDelete);
                        }

                    }

                }
            }

            $attach = null;

            //format error log
            if (!empty($this->errorStore) || !empty($this->errorServiceUser)) {
                ksort($this->errorStore);
                ksort($this->errorServiceUser);
                Excel::store(
                    new ErrorExport($this->errorStore, $this->errorServiceUser),
                    'import_result.xlsx',
                    'local'
                );
                $attach = \Storage::disk('local')->path('import_result.xlsx');
            }

            //send mail
            $mailTemplate = $mailTemplateRepo->getMailTemplateByCode(MailTemplate::IMPORT_H2);
            $mailSubject = str_replace('[[YYYY/MM]]', Carbon::now()->format('Y/m'), $mailTemplate->subject);
            $mailBody = str_replace('[[YYYY/MM]]', Carbon::now()->format('Y/m'), $mailTemplate->body);

            \Mail::send([], [], function ($message) use ($attach, $mailSubject, $mailBody) {
                $message->to(config('mail.support_office_email'))
                    ->subject($mailSubject)
                    ->setBody($mailBody, 'text/html');
                if (!is_null($attach)) {
                    $message->attach($attach);
                }
            });
        }

        return true;
    }
}
