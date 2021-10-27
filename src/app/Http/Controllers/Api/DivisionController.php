<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Resources\DivisionCollection;
use App\Http\Resources\BasicDivisionCollection;
use App\Http\Resources\DivisionResource;
use App\Repositories\DivisionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{
    protected $divisionRepository;

    public function __construct(DivisionRepository $divisionRepository)
    {
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * Get list organizations
     */
    public function index(Request $request)
    {
        $divisions = $this->divisionRepository->getList($request);
        if ($request->limit_data) {
            return responseOK(new BasicDivisionCollection($divisions));
        }
        return responseOK(new DivisionCollection($divisions));
    }

    /**
     * Show the info for the given organization.
     */
    public function show($id)
    {
        $division = $this->divisionRepository->findOrFail($id);
        return responseOK(new DivisionResource($division));
    }

    /**
     * Update the given organization
     */
    public function update($id, StoreDivisionRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->divisionRepository->update($id, $request->only(['branch_id', 'name']));
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Delete the given organization
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->divisionRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }
}
