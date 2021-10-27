<?php


namespace App\Repositories;


use App\Models\MailTemplate;

class MailTemplateRepository extends BaseRepository
{
    public function getModel()
    {
        return MailTemplate::class;
    }

    public function getList($request)
    {
        return $this->model->with('mailDocuments')->orderBy('id', 'DESC')
            ->paginate($request->limit ? $request->limit : 9999999);
    }

    public function getMailTemplatesByIds($arrayIds)
    {
        return $this->model->whereIn('id', $arrayIds)->with('mailDocuments')->get();
    }

    public function getMailTemplateByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getTemplateWithMailDocument($id)
    {
        return $this->model->where('id', $id)->with('mailDocuments')->first();
    }
}
