<?php

namespace App\Console\Commands;

use App\Helper\Constant;
use App\Mail\DocumentMailAlert;
use App\Models\DocumentType;
use App\Models\MailDocument;
use App\Models\MailTemplate;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\MailDocumentHistoryRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\MailTemplateRepository;
use App\Repositories\DocumentRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAlertDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Alert Default';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //check mail alert status
        $generalSettingRepo = new GeneralSettingRepository();

        if (!$generalSettingRepo->getGeneralSetting()->send_alert) {
            Log::error('Send Alert is Turn Off!');
            return false;
        }

        $mailDocumentRepository = new MailDocumentRepository();
        $mailDocumentHistoryRepository = new MailDocumentHistoryRepository();
        $mailTemplateRepo = new MailTemplateRepository();
        $documentRepo = new DocumentRepository();

        //NamNB;
        $mailHistory = [];
        $mailTemplate = $mailTemplateRepo->filter([['is_system', '=', MailTemplate::IS_SYSTEM]]);
        $mailDocument = $mailDocumentRepository->getAllMailDocumentAuto();
        $documents = $documentRepo->getDocumentByAlert()->toArray();

        //build mail template by code
        $mailTemplates = [];
        foreach ($mailTemplate as $item) {
            $mailTemplates[$item->code] = [
                'id' => $item->id,
                'subject' => $item->subject,
                'body' => $item->body
            ];
        }

        //build mail document
        $mailDocuments = [];
        foreach ($mailDocument as $item) {
            $mailDocuments[$item->document_id] = $item->toArray();
        }

        //buil data send mail
        $now = Carbon::now()->format('Y-m-d');
        $monthNow = Carbon::now()->format('m');
        $dateNow = Carbon::now()->format('d');
        $yearsAgo = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
        $dataSendAlert = [];
        foreach ($documents as $item) {
            if (empty($item['document_object'])) {
                $item['document_object']['code'] = -1;
            }

            $stopSendMail = false;
            if (!empty($item['service_user'])) {
                $fileSetManagements = $item['service_user']['file_set_managements'];
                foreach ($fileSetManagements as $fileSetManagement) {
                    if ($item['store_id'] == $fileSetManagement['store_id'] &&
                        $item['document_type_id'] == $fileSetManagement['document_type_id'] &&
                        $item['service_user_id'] == $fileSetManagement['service_user_id']
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
            $templateCode = null;

            //expiry date
            $endDate = Carbon::parse($item['attributes'][0]['pivot']['end_date'])->format('Y-m-d');
            if ($endDate == $now && $now > $mailDocuments[$item['id']]['last_send_at']
                && (
                    in_array(
                        $item['document_type']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2B']['expiry_date']
                    )
                    || in_array(
                        $item['document_object']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2C']['expiry_date']
                    )
                )
            ) {
                if (!$item['service_user_id']) {
                    $templateCode = MailTemplate::B2B_EXPIRATION_DATE;
                } else {
                    $templateCode = MailTemplate::B2C_EXPIRATION_DATE;
                }

                $dataSendAlert[] = [
                    'document_info' => $item,
                    'mail_document' => $mailDocuments[$item['id']],
                    'mail_template' => $mailTemplates[$templateCode]
                ];
                continue;
            }

            //before 1 month
            $endDateSub1Month = Carbon::parse($item['attributes'][0]['pivot']['end_date'])
                ->subDays(Constant::NUMBER_OF_DAYS_DEFAULT)
                ->format('m');
            if ($endDateSub1Month == $monthNow && $dateNow == config('mail.day_send_mail')
                && (
                    in_array(
                        $item['document_type']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2B']['before_1_month']
                    )
                    || in_array(
                        $item['document_object']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2C']['before_1_month']
                    )
                )
            ) {
                if (!$item['service_user_id']) {
                    $templateCode = MailTemplate::B2B_BEFORE_1_MONTH;
                } else {
                    $templateCode = MailTemplate::B2C_BEFORE_1_MONTH;
                }

                $dataSendAlert[] = [
                    'document_info' => $item,
                    'mail_document' => $mailDocuments[$item['id']],
                    'mail_template' => $mailTemplates[$templateCode]
                ];
                continue;
            }

            //before 4 month
            $endDateSub4Month = Carbon::parse($item['attributes'][0]['pivot']['end_date'])
                ->subDays(Constant::NUMBER_OF_DAYS_DEFAULT * 4)
                ->format('m');
            if ($endDateSub4Month == $monthNow && $dateNow ==
                config('mail.day_send_mail') && !$item['service_user_id']
                && in_array(
                    $item['document_type']['code'],
                    Constant::CATEGORY_DOCUMENT_ALERT['B2B']['before_4_month']
                )
            ) {
                $dataSendAlert[] = [
                    'document_info' => $item,
                    'mail_document' => $mailDocuments[$item['id']],
                    'mail_template' => $mailTemplates[MailTemplate::B2B_BEFORE_4_MONTH]
                ];
                continue;
            }

            //out of date
            if ($now > $endDate
                && (
                    in_array(
                        $item['document_type']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2B']['out_of_date']
                    )
                    || in_array(
                        $item['document_object']['code'],
                        Constant::CATEGORY_DOCUMENT_ALERT['B2C']['out_of_date']
                    )
                )
            ) {
                $lastSendDate = $mailDocuments[$item['id']]['last_send_at'] ?? $endDate;
                $dayDiff = Carbon::parse($lastSendDate)->diffInDays(Carbon::now());
                if ($dayDiff > 0 && $dayDiff % Constant::NUMBER_OF_DAYS_DEFAULT == 0) {
                    if (!$item['service_user_id']) {
                        $templateCode = MailTemplate::B2B_AFTER_1_MONTH;
                    } else {
                        $templateCode = MailTemplate::B2C_AFTER_1_MONTH;
                    }

                    $dataSendAlert[] = [
                        'document_info' => $item,
                        'mail_document' => $mailDocuments[$item['id']],
                        'mail_template' => $mailTemplates[$templateCode]
                    ];
                }

                continue;
            }
        }

        //send alert document
        foreach ($dataSendAlert as $item) {
            $keyword = array_values(Constant::VARIABLE_IN_MAIL);
            $linkB2B = Config::get('app.url') . '/b-to-b/' . $item['document_info']['id'];
            $linkB2C = empty($item['document_info']['service_user']) ?
                '' : Config::get('app.url') . '/b-to-c/' . $item['document_info']['id'] .
                '?sid=' . $item['document_info']['service_user']['id'];
            $valueReplace = [
                $item['document_info']['name'],
                $item['document_info']['basic_store']['name'],
                '<a href="' . $linkB2B . '" target="_blank">' . $linkB2B . '</a>',
                empty($item['document_info']['service_user']) ?
                    '' : htmlentities($item['document_info']['service_user']['name']),
                '',
                empty($item['document_info']['service_user']) ? '' : '<a href="' .
                    $linkB2C . '" target="_blank">' . $linkB2C . '</a>',
                $item['document_info']['document_type']['name'],
                (!empty($item['document_info']['document_object'])
                    && isset($item['document_info']['document_object']['name']))
                    ? $item['document_info']['document_object']['name'] : ''
            ];

            $mailSubject = str_replace($keyword, $valueReplace, $item['mail_template']['subject']);
            $mailContent = str_replace($keyword, $valueReplace, $item['mail_template']['body']);

            // send mail alert
            $mail = Mail::to(trimAllItemInSerializeString($item['mail_document']['to']));
            if ($item['mail_document']['cc']) {
                $mail->cc(trimAllItemInSerializeString($item['mail_document']['cc']));
            }
            if ($item['mail_document']['bcc']) {
                $mail->bcc(trimAllItemInSerializeString($item['mail_document']['bcc']));
            }
            $mail->send(new DocumentMailAlert($mailSubject, $mailContent));

            //update last send
            $mailDocumentRepository->update($item['mail_document']['id'], ['last_send_at' => $now]);

            // build log send mail
            $mailHistory[] = [
                'mail_document_id' => $item['mail_document']['id'],
                'document_id' => $item['document_info']['id'],
                'mail_template_id' => $item['mail_template']['id'],
                'to' => $item['mail_document']['to'],
                'cc' => $item['mail_document']['cc'],
                'bcc' => $item['mail_document']['bcc'],
                'type' => $item['mail_document']['type'],
                'is_repeated' => $item['mail_document']['is_repeated'],
                'repeat_unit' => $item['mail_document']['repeat_unit'],
                'repeat_value' => $item['mail_document']['repeat_value'],
                'send_at' => $item['mail_document']['send_at'],
                'last_send_at' => $now,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        //save log send mail
        if (!empty($mailHistory)) {
            $mailHistoryChunk = array_chunk($mailHistory, 2000);
            foreach ($mailHistoryChunk as $item) {
                $mailDocumentHistoryRepository->insertMany($item);
            }
        }

        //finish send alert
        Log::info('Send alert mail success');
    }
}
