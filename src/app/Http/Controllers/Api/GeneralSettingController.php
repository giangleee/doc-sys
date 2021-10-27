<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSettingRequest;
use App\Http\Resources\GeneralSettingCollection;
use App\Repositories\GeneralSettingRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GeneralSettingController extends Controller
{
    //
    protected $generalSettingRepository;

    public function __construct(GeneralSettingRepository $generalSettingRepository)
    {
        $this->generalSettingRepository = $generalSettingRepository;
    }

    /**
     * Show the info for the general settings
     */
    public function view()
    {
        return responseOK(new GeneralSettingCollection($this->generalSettingRepository->getGeneralSetting()));
    }

    /**
     * Update the info for the general settings
     */
    public function update(GeneralSettingRequest $request)
    {
        try {
            $this->generalSettingRepository->updateGeneralSetting($request);
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            return responseError(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }
}
