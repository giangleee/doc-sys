<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\CreateResource;
use App\Http\Resources\TestCollection;
use App\Http\Resources\TestResource;
use App\Repositories\TestRepository;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $testRepository;

    public function __construct(TestRepository $testRepo)
    {
        $this->testRepository = $testRepo;
    }

    public function index(Request $request)
    {
        $lists = $this->testRepository->search($request);
        return responseOK(new TestCollection($lists));
    }


    public function show($id)
    {
        $test = $this->testRepository->findOrFail($id);
        return responseOK(new TestResource($test));
    }

    public function store(CreateRequest $request)
    {
        $requestData = $request->only([
            'name',
            'ad_cd',
            'registration_user_type_id',
            'operator_id'
        ]);
        $test = $this->testRepository->store($requestData);
        return responseCreated(new CreateResource($test));
    }

    public function update($id, UpdateRequest $request)
    {
        $test = $this->testRepository->findOrFail($id);
        $this->authorize('update', $test);
        $requestData = $request->only(['name', 'ad_cd', 'registration_user_type_id']);
        $this->testRepository->update($test, $requestData);
        return responseUpdatedOrDeleted();
    }

    public function delete($id)
    {
        $test = $this->testRepository->findOrFail($id);
        $this->testRepository->delete($test);
        return responseUpdatedOrDeleted();
    }
}
