<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeCollection;
use App\Http\Resources\BasicOfficeCollection;
use App\Http\Resources\OfficeResource;
use App\Repositories\OfficeRepository;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    protected $officeRepository;

    public function __construct(OfficeRepository $officeRepository)
    {
        $this->officeRepository = $officeRepository;
    }

    /**
     * Get list organizations
     */
    public function index(Request $request)
    {
        $offices = $this->officeRepository->getList($request);
        if ($request->limit_data) {
            return responseOK(new BasicOfficeCollection($offices));
        }
        return responseOK(new OfficeCollection($offices));
    }

    /**
     * Show the info for the given organization.
     */
    public function show($id)
    {
        $office = $this->officeRepository->findOrFail($id);
        return responseOK(new OfficeResource($office));
    }
}
