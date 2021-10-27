<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Resources\BranchCollection;
use App\Http\Resources\BranchResource;
use App\Repositories\BranchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    protected $branchRepository;

    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * Get list branches
     */
    public function index(Request $request)
    {
        $branches = $this->branchRepository->getList($request);
        return responseOK(new BranchCollection($branches));
    }

    /**
     * Show the info for the given branch.
     */
    public function show($id)
    {
        $branch = $this->branchRepository->findOrFail($id);
        return responseOK(new BranchResource($branch));
    }

    /**
     * Update the given branch
     */
    public function update($id, StoreBranchRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->branchRepository->update($id, $request->only(['name']));
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Delete the given branch
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->branchRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }
}
