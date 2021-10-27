<?php

namespace App\Console\Commands;

use App\Helper\Constant;
use App\Mail\DocumentMailAlert;
use App\Models\MailTemplate;
use App\Repositories\DocumentRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\MailDocumentHistoryRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\MailTemplateRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAlertMailWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $generalSettingRepo = new GeneralSettingRepository();
        $documentRepo = new DocumentRepository();
        $mailDocumentHistoryRepository = new MailDocumentHistoryRepository();
        $mailTemplateRepo = new MailTemplateRepository();
        $mailDocumentRepository = new MailDocumentRepository();

        $generalSettingDetail = $generalSettingRepo->getGeneralSetting();
        if (!$generalSettingDetail->send_alert) {
            \Log::error('Send mail alert is turn off');
            return false;
        }

        $now = Carbon::now()->format('Y-m-d');
        $dayDiff = Carbon::parse(Carbon::parse($generalSettingDetail->exchange_deadline)
            ->format('Y-m-d'))->diffInDays($now);
        $yearsAgo = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d');
        if ($generalSettingDetail->exchange_deadline
            && $now > formatDate($generalSettingDetail->exchange_deadline)
            && $dayDiff % Constant::NUMBER_OF_DAYS_DEFAULT == 0) {

            $documentImportantMatter =
                $documentRepo->getDocumentImportantMatter($generalSettingDetail->exchange_deadline);
            $mailTemplateDetail =
                $mailTemplateRepo->getMailTemplateByCode(MailTemplate::B2C_AFTER_1_MONTH);

            foreach ($documentImportantMatter as $item) {
                $stopSendMail = false;

                if (!empty($item->serviceUser)) {
                    $fileSetManagements = $item->serviceUser->fileSetManagements;
                    foreach ($fileSetManagements as $fileSetManagement) {
                        if ($item->store_id == $fileSetManagement->store_id &&
                            $item->document_type_id == $fileSetManagement->document_type_id &&
                            $item->service_user_id == $fileSetManagement->service_user_id
                        ) {
                            if (!empty($fileSetManagement->contract_cancel_date) &&
                                $yearsAgo > Carbon::parse($fileSetManagement->contract_cancel_date)) {
                                $stopSendMail = true;
                                break;
                            }
                        }
                    }

                    if ($stopSendMail) {
                        continue;
                    }
                }

                $keyword = array_values(Constant::VARIABLE_IN_MAIL);
                $linkB2B = Config::get('app.url') . '/b-to-b/' . $item->id;
                $linkB2C = Config::get('app.url') . '/b-to-c/' . $item->id . '?sid=' . $item->serviceUser->id;
                $valueReplace = [
                    $item->name,
                    empty($item->store->name) ? '' : $item->store->name,
                    '<a href="' . $linkB2B . '" target="_blank">' . $linkB2B . '</a>',
                    htmlentities($item->serviceUser->name),
                    '',
                    '<a href="' . $linkB2C . '" target="_blank">' . $linkB2C . '</a>',
                    empty($item->documentType->name) ? '' : $item->documentType->name,
                    (!empty($item->documentObject)
                        && isset($item->documentObject->name))
                        ? $item->documentObject->name : ''
                ];
                $mailSubject = str_replace($keyword, $valueReplace, $mailTemplateDetail->subject);
                $mailContent = str_replace($keyword, $valueReplace, $mailTemplateDetail->body);
                $mail = Mail::to($item->store->email);
                $mail->cc(config('mail.support_office_email'));

                $mail->send(new DocumentMailAlert($mailSubject, $mailContent));

                $mailDocumentRepository->update($item->mailDocument->id, ['last_send_at' => $now]);

                $mailHistory[] = [
                    'mail_document_id' => $item->mailDocument->id,
                    'document_id' => $item->id,
                    'mail_template_id' => $mailTemplateDetail['id'],
                    'to' => $item->store->email,
                    'cc' => config('mail.support_office_email'),
                    'bcc' => null,
                    'type' => $item->mailDocument->type,
                    'is_repeated' => $item->mailDocument->is_repeated,
                    'repeat_unit' => $item->mailDocument->repeat_unit,
                    'repeat_value' => $item->mailDocument->repeat_value,
                    'send_at' => $item->mailDocument->send_at,
                    'last_send_at' => $now,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            if (!empty($mailHistory)) {
                $mailDocumentHistoryRepository->insertMany($mailHistory);
            }
        }

        //finish send alert
        Log::info('Send alert mail success');

    }
}
