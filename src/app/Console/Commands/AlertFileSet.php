<?php

namespace App\Console\Commands;

use App\Helper\Constant;
use App\Mail\DocumentMailAlert;
use App\Models\DocumentObject;
use App\Models\DocumentType;
use App\Models\MailTemplate;
use App\Repositories\DocumentObjectRepository;
use App\Repositories\FileSetManagementRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\MailTemplateRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertFileSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:fileset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert File Set Missing Document';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $generalSettingRepo = new GeneralSettingRepository();
        if (!$generalSettingRepo->getGeneralSetting()->send_alert) {
            Log::error("Send Alert Is Turn Off!");
            return false;
        }

        $mailTemplateRepo = new MailTemplateRepository();
        $documentObjectRepo = new DocumentObjectRepository();
        $fileSetManagementRepo = new FileSetManagementRepository();

        //Get file set with service user
        $fileSetManagementCollection = $fileSetManagementRepo->getFileSetWithServiceUser();
        $fileSetWithServiceUser = $fileSetManagementCollection->toArray();

        //get service user and office
        $fileSetAlert = $fileSetManagementCollection->mapWithKeys(function ($item) {
            return [$item['id'] => $item];
        })->toArray();
        $documentObjectsCollection = $documentObjectRepo->getAll();
        $mailTemplate = $mailTemplateRepo->getMailTemplateByCode(MailTemplate::B2C_MISSING_DOCUMENT)->toArray();

        //build array service user
        $fileSetDocuments = $serviceUserDocumentObjects = [];
        $yearsAgo = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
        foreach ($fileSetWithServiceUser as $item) {
            if (!empty($item['contract_cancel_date']) && $yearsAgo > Carbon::parse($item['contract_cancel_date'])) {
                continue;
            }
            if (!empty($item['service_user_id']) && empty($item['service_user'])) {
                continue;
            }
            if (!is_null($item['documents_id']) && is_null($item['documents_deleted_at'])) {
                if (isset($item['document_objects_id']) && !empty($item['document_objects_id'])) {
                    $fileSetDocuments[$item['service_user_id']][$item['id']]
                    [$item['document_type']['code']]
                    [$item['document_objects_id']]
                        = $item['document_objects_code'];
                    $serviceUserDocumentObjects[$item['service_user_id']]
                    [$item['document_type']['code']]
                    [$item['document_objects_id']]
                        = $item['document_objects_code'];
                }
            } else {
                $fileSetDocuments[$item['service_user_id']][$item['id']]
                [$item['document_type']['code']] = [];
                $serviceUserDocumentObjects[$item['service_user_id']]
                [$item['document_type']['code']] = [];
            }
        }
        //build data alert file set.
        $dataSendAlert = [];
        foreach ($fileSetDocuments as $serviceUserId => $fileSetDocument) {
            foreach ($fileSetDocument as $code => $item) {
                $homeCareMissing = $homeNursingMissing = $welfareCenterMissing = [];
                if (isset($item[DocumentType::HOME_CARE]) &&
                    isset($serviceUserDocumentObjects[$serviceUserId][DocumentType::HOME_CARE])
                ) {
                    $docObjectHomeCareExisted = array_unique(array_merge(
                        $item[DocumentType::HOME_CARE],
                        $serviceUserDocumentObjects[$serviceUserId][DocumentType::HOME_CARE]
                    ));
                    $homeCareMissing = array_diff(
                        Constant::DOCUMENT_OBJECT_MUST_IN_FILE_SET[DocumentType::HOME_CARE],
                        $docObjectHomeCareExisted
                    );
                }

                if (isset($item[DocumentType::HOME_NURSING])) {
                    if (isset($item[DocumentType::HOME_NURSING]) &&
                        isset($serviceUserDocumentObjects[$serviceUserId][DocumentType::HOME_NURSING])
                    ) {
                        $docObjectHomeNursingExisted = array_unique(array_merge(
                            $item[DocumentType::HOME_NURSING],
                            $serviceUserDocumentObjects[$serviceUserId][DocumentType::HOME_NURSING]
                        ));
                        $homeNursingMissing = array_diff(
                            Constant::DOCUMENT_OBJECT_MUST_IN_FILE_SET[DocumentType::HOME_NURSING],
                            $docObjectHomeNursingExisted
                        );
                    }
                }

                if (isset($item[DocumentType::WELFARE_CENTER])) {
                    if (isset($item[DocumentType::WELFARE_CENTER]) &&
                        isset($serviceUserDocumentObjects[$serviceUserId][DocumentType::WELFARE_CENTER])
                    ) {
                        $docObjectWelfareExisted = array_unique(array_merge(
                            $item[DocumentType::WELFARE_CENTER],
                            $serviceUserDocumentObjects[$serviceUserId][DocumentType::WELFARE_CENTER]
                        ));
                        $welfareCenterMissing = array_diff(
                            Constant::DOCUMENT_OBJECT_MUST_IN_FILE_SET[DocumentType::WELFARE_CENTER],
                            $docObjectWelfareExisted
                        );
                    }
                }

                if (!empty($homeCareMissing)) {
                    //get name document object missing
                    $nameHomeCareMissing = $documentObjectsCollection->filter(function ($item) use ($homeCareMissing) {
                        return in_array($item->code, $homeCareMissing);
                    })->pluck('name')->toArray();

                    //build data alert
                    $dataSendAlert[] = [
                        'file_set_management' => $fileSetAlert[$code],
                        'document_missing' => $nameHomeCareMissing,
                        'mail_template' => $mailTemplate
                    ];
                }
                if (!empty($homeNursingMissing)) {
                    //get name document object missing
                    $nameHomeNursingMissing = $documentObjectsCollection
                        ->filter(function ($item) use ($homeNursingMissing) {
                            return in_array($item->code, $homeNursingMissing);
                        })
                        ->pluck('name')->toArray();

                    //build data alert
                    $dataSendAlert[] = [
                        'file_set_management' => $fileSetAlert[$code],
                        'document_missing' => $nameHomeNursingMissing,
                        'mail_template' => $mailTemplate
                    ];
                }

                if (!empty($welfareCenterMissing)) {
                    //get name document object missing
                    $nameWelfareCenterMissing = $documentObjectsCollection
                        ->filter(function ($item) use ($welfareCenterMissing) {
                            return in_array($item->code, $welfareCenterMissing);
                        })
                        ->pluck('name')->toArray();

                    //build data alert
                    $dataSendAlert[] = [
                        'file_set_management' => $fileSetAlert[$code],
                        'document_missing' => $nameWelfareCenterMissing,
                        'mail_template' => $mailTemplate
                    ];
                }
            }
        }
        //send alert document
        foreach ($dataSendAlert as $item) {
            $serviceUserId = $item['file_set_management']['service_user_id'];
            $documentTypeId = $item['file_set_management']['document_type_id'];
            $storeId = $item['file_set_management']['store_id'];
            $documentsInFilesSet = $fileSetManagementCollection
                ->filter(function ($fileSetManagement) use ($serviceUserId, $documentTypeId, $storeId) {
                    return $fileSetManagement->service_user_id == $serviceUserId &&
                        $fileSetManagement->document_type_id == $documentTypeId &&
                        $fileSetManagement->store_id == $storeId &&
                        $fileSetManagement->documents_deleted_at == null;
                })->pluck('documents_id')->toArray();
            if (!isset($documentsInFilesSet[0])) {
                $linkB2C = Config::get('app.url') . '/b-to-c?sid=' . $serviceUserId .
                    '&store_id=' . $item['file_set_management']['store_id'] .
                    '&document_type_id=' . $item['file_set_management']['document_type_id'];
            } else {
                $linkB2C = Config::get('app.url') . '/b-to-c/' . $documentsInFilesSet[0] .
                    '?sid=' . $serviceUserId . '&store_id=' . $item['file_set_management']['store_id'] .
                    '&document_type_id=' . $item['file_set_management']['document_type_id'];
            }
            $keyword = array_values(Constant::VARIABLE_IN_MAIL);
            $documentMissing = implode('、', $item['document_missing']);
            $valueReplace = [
                '',
                $item['file_set_management']['store']['name'],
                '',
                htmlentities($item['file_set_management']['service_user']['name']),
                $documentMissing,
                '<a href="' . $linkB2C . '" target="_blank">' . $linkB2C . '</a>',
                '',
                ''
            ];
            $mailSubject = str_replace($keyword, $valueReplace, $item['mail_template']['subject']);
            $mailContent = str_replace($keyword, $valueReplace, $item['mail_template']['body']);

            $mailSubject = html_entity_decode($mailSubject);
            $mail = Mail::to($item['file_set_management']['store']['email']);
            $mail->send(new DocumentMailAlert($mailSubject, $mailContent));
        }
        Log::info('Send alert FileSet success!');
    }
}
