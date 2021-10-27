<?php


namespace App\Repositories;


use App\Helper\Constant;
use App\Models\MailDocument;
use Carbon\Carbon;

class MailDocumentRepository extends BaseRepository
{
    public function getModel()
    {
        return MailDocument::class;
    }

    public function settingAlertMail($document, $params)
    {
        $mailDocument = $document->mailDocument;
        $mailCc = explode(',', $params['cc']);
        if (!in_array(config('mail.support_office_email'), $mailCc)) {
            array_push($mailCc, config('mail.support_office_email'));
        }
        $dataSave = [
            'document_id' => $document->id,
            'mail_template_id' => null,
            'to' => $params['to'],
            'cc' => implode(',', $mailCc),
            'bcc' => $params['bcc'],
            'type' => $params['send_type'],
            'send_at' => null
        ];
        if ($params['send_type'] == MailDocument::SEND_TYPE_MANUAL) {
            $dataSave['mail_template_id'] = $params['mail_template'];
            $dataSave['send_at'] = Carbon::createFromTimestamp(strtotime($params['send_date']))->format('Y-m-d');
            $dataSave += [
                'is_repeated' => $params['send_interval'] ? MailDocument::REPEAT : MailDocument::NOT_REPEAT,
                'repeat_unit' => $params['send_interval'] ? $params['send_unit'] : null,
                'repeat_value' => $params['send_interval'] ? $params['send_value'] : null,
                'last_send_at' => null
            ];
        }
        $this->update($mailDocument->id, $dataSave);
    }

    public function saveMailDocument($document)
    {
        $storeRepo = new StoreRepository();
        $documentStore = $storeRepo->findOrFail($document->store_id);

        $dataSave = [
            'document_id' => $document->id,
            'to' => $documentStore->email,
            'cc' => config('mail.support_office_email'),
        ];

        $this->create($dataSave);
    }

    public function getAllMailDocumentByType($type = null)
    {
        if (!empty($type)) {
            return $this->model->where('type', $type)
                ->with('document.serviceUser.fileSetManagements', 'mailTemplate')
                ->get();
        }
        return $this->model->with('document.serviceUser.fileSetManagements', 'mailTemplate')->get();
    }

    public function getDocumentHasAttributeValidityPeriod($type)
    {
        return $this->model->where('type', $type)
            ->with('documentAlert.attributeMails')
            ->whereHas('documentAlert.attributeMails')
            ->get();
    }
    public function deleteMailDocumentByDocumentID($documentID)
    {
        return $this->model->where('document_id', $documentID)->delete();
    }

    public function getAllMailDocumentAuto()
    {
        return $this->model->where('type', MailDocument::SEND_TYPE_AUTO)->get();
    }

    public function getMailDocumentById($idDocument)
    {
        return $this->model->where('document_id', $idDocument)->first();
    }
}
