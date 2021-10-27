<?php


namespace App\Repositories;


use App\Models\MailDocumentHistory;

class MailDocumentHistoryRepository extends BaseRepository
{
    public function getModel()
    {
        return MailDocumentHistory::class;
    }

    public function save($data, $lastSendAt)
    {
        return $this->create([
            'mail_document_id' => $data->id,
            'document_id' => $data->document_id,
            'mail_template_id' => $data->mail_template_id,
            'to' => $data->to,
            'cc' => $data->cc,
            'bcc' => $data->bcc,
            'type' => $data->type,
            'is_repeated' => $data->is_repeated,
            'repeat_unit' => $data->repeat_unit,
            'repeat_value' => $data->repeat_value,
            'send_at' => $data->send_at,
            'last_send_at' => $lastSendAt
        ]);
    }
}
