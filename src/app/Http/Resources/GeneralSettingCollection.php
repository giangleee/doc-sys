<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class GeneralSettingCollection extends JsonResource
{
    public function toArray($request)
    {
        return [
            'send_alert' => $this->send_alert,
            'exchange_deadline' => formatDate($this->exchange_deadline),
        ];
    }
}
