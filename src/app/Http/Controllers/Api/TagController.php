<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Repositories\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return responseOK(new TagCollection($this->tagRepository->getList($request)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        DB::beginTransaction();
        try {
            $tag = $this->tagRepository->create($request->only(['name']));
            DB::commit();
            return responseCreated(new TagResource($tag));
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tag = $this->tagRepository->findOrFail($id);
        return responseOK(new TagResource($tag));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, StoreTagRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->tagRepository->update($id, $request->only(['name']));
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->tagRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }
}
