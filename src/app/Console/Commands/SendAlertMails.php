<?php

namespace App\Console\Commands;

use App\Helper\Constant;
use App\Mail\DocumentMailAlert;
use App\Models\MailDocument;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\MailDocumentHistoryRepository;
use App\Repositories\MailDocumentRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAlertMails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send';

    /**
     * The console command description.
     */
    protected $description = 'Send alert mail to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $generalSettingRepo = new GeneralSettingRepository();
        if (!$generalSettingRepo->getGeneralSetting()->send_alert) {
            \Log::error('Send mail alert is turn off');
            return false;
        }

        $mailDocumentRepository = new MailDocumentRepository();
        $mailDocumentHistoryRepository = new MailDocumentHistoryRepository();

        $manualType = MailDocument::SEND_TYPE_MANUAL;
        $documentSendMailManually = $mailDocumentRepository->getAllMailDocumentByType($manualType);
        $now = Carbon::now()->format('Y-m-d');
        $yearsAgo = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
        foreach ($documentSendMailManually as $mailDocument) {
            if (!isset($mailDocument->document)) {
                continue;
            }

            $docInfo = $mailDocument->document->toArray();
            $stopSendMail = false;

            if (!empty($docInfo['service_user'])) {
                $fileSetManagements = $docInfo['service_user']['file_set_managements'];
                foreach ($fileSetManagements as $fileSetManagement) {
                    if ($docInfo['store_id'] == $fileSetManagement['store_id'] &&
                        $docInfo['document_type_id'] == $fileSetManagement['document_type_id'] &&
                        $docInfo['service_user_id'] == $fileSetManagement['service_user_id']
                    ) {
                        if (!empty($fileSetManagement['contract_cancel_date']) &&
                            $yearsAgo > Carbon::parse($fileSetManagement['contract_cancel_date'])) {
                            $stopSendMail = true;
                            break;
                        }
                    }
                }

                if ($stopSendMail) {
                    continue;
                }
            }

            // get all objects to send mail
            $objects = [
                'to' => !empty($mailDocument->to) ? trimAllItemInSerializeString($mailDocument->to) : [],
                'cc' => !empty($mailDocument->cc) ? trimAllItemInSerializeString($mailDocument->cc) : [],
                'bcc' => !empty($mailDocument->bcc) ? trimAllItemInSerializeString($mailDocument->bcc) : []
            ];
            // get mail template
            $mailTemplate = $mailDocument->mailTemplate;

            // get document info
            $documentInfo = $mailDocument->document;

            $documentType = $documentInfo->documentType;

            $documentObject = $documentInfo->documentObject;
            $documentObjectName = !empty($documentObject) ? $documentObject->name : '';

            // check time to send mail
            $sendTime = formatDate($mailDocument->send_at);
            //Nam start code
            if ($mailDocument->is_repeated) {
                $repeatValue = $mailDocument->repeat_value;
                $numberOfDays = Constant::NUMBER_OF_DAYS[$mailDocument->repeat_unit];
                if (!empty($mailDocument->last_send_at)) {
                    $lastSendAt = formatDate($mailDocument->last_send_at);
                    $sendTime = getTimeToSendMail($lastSendAt, $repeatValue, $numberOfDays);
                } else {
                    if ($sendTime < $now) {
                        $diff = Carbon::parse($sendTime)->diffInDays(Carbon::parse($now));
                        if ($diff % $numberOfDays == 0) {
                            $sendTime = getTimeToSendMail($sendTime, $repeatValue, $diff / $repeatValue);
                        }
                    }
                }
            } else {
                if (!empty($mailDocument->last_send_at)) {
                    continue;
                }
            }
            //Nam finish code
            if ($sendTime == $now) {
                $mailSubject = $mailTemplate->subject;
                $mailBody = $mailTemplate->body;

                // get info document to replace param in mail template
                $documentName = $documentInfo->name;
                $officeName = !empty($documentInfo->store_id) ? $documentInfo->store->name : '';
                $serviceUserName = !empty($documentInfo->service_user_id) ? $documentInfo->serviceUser->name : '';

                $urlLinkToDocumentDetail = Config::get('app.url') . '/b-to-b/' . $documentInfo->id;
                $urlDocumentDetail = '<a href="' . $urlLinkToDocumentDetail . '">';
                $urlDocumentDetail .= $urlLinkToDocumentDetail;
                $urlDocumentDetail .= '</a>';

                if (in_array($documentType->code, Constant::IS_B2C)) {
                    $urlLinkToFileSet = Config::get('app.url') . '/b-to-c/' . $documentInfo->id;
                    $urlLinkToFileSet .= '?sid=' . $documentInfo->service_user_id;
                    $urlFileSet = '<a href="' . $urlLinkToFileSet . '">';
                    $urlFileSet .= $urlLinkToFileSet;
                    $urlFileSet .= '</a>';
                    $docInFileSet = '';
                } else {
                    $urlFileSet = '';
                    $docInFileSet = '';
                }

                $stringNeedToReplace = Constant::VARIABLE_IN_MAIL;
                $strToReplace = [
                    $documentName,
                    $officeName,
                    $urlDocumentDetail,
                    $serviceUserName,
                    $docInFileSet,
                    $urlFileSet,
                    $documentType->name,
                    $documentObjectName
                ];
                $mailSubject = str_replace($stringNeedToReplace, $strToReplace, $mailSubject);
                $mailBody = str_replace($stringNeedToReplace, $strToReplace, $mailBody);

                // send mail alert

                $mail = Mail::to($objects['to']);
                if (!empty($objects['cc'])) {
                    $mail->cc($objects['cc']);
                }
                if (!empty($objects['bcc'])) {
                    $mail->bcc($objects['bcc']);
                }
                $mail->send(new DocumentMailAlert($mailSubject, $mailBody));

                // save time send mail nearest
                $mailDocumentRepository->update($mailDocument->id, ['last_send_at' => $now]);

                // save log send mail
                $mailDocumentHistoryRepository->save($mailDocument, $now);
            }
        }
    }
}
