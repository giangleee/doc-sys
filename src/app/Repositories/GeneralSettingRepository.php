<?php


namespace App\Repositories;


use App\Http\Requests\GeneralSettingRequest;
use App\Models\GeneralSetting;

class GeneralSettingRepository extends BaseRepository
{

    /**
     * @inheritDoc
     */
    public function getModel()
    {
        return GeneralSetting::class;
    }

    public function getGeneralSetting()
    {
        return $this->model->first();
    }

    public function updateGeneralSetting(GeneralSettingRequest $request)
    {
        return $this->update($this->model->first()->id, $request->all());
    }


}
