<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BasicStoreCollection;
use App\Http\Resources\StoreCollection;
use App\Http\Resources\StoreResource;
use App\Repositories\StoreRepository;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get list stores
     */
    public function index(Request $request)
    {
        $stores = $this->storeRepository->getList($request);
        if ($request->limit_data) {
            return responseOK(new BasicStoreCollection($stores));
        }
        return responseOK(new StoreCollection($stores));
    }

    /**
     * Show the info for the given store.
     */
    public function show($id)
    {
        $store = $this->storeRepository->findOrFail($id);
        return responseOK(new StoreResource($store));
    }
}
