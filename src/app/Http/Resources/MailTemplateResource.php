<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class MailTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        $is_used = false;
        if (!empty($this->mailDocuments->toArray())) {
            $is_used = true;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'body' => $this->body,
            'user_created' => $this->userCreated,
            'user_updated' => $this->userUpdated,
            'is_system' => $this->is_system,
            'mail_document' => $this->mailDocuments,
            'is_used' => $is_used,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
