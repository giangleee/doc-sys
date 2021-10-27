<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionCollection;
use App\Repositories\PositionRepository;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected $positionRepository;

    public function __construct(PositionRepository $positionRepository)
    {
        $this->positionRepository = $positionRepository;
    }

    /**
     * Get list positions
     */
    public function index(Request $request)
    {
        $positions = $this->positionRepository->getList($request);
        return responseOK(new PositionCollection($positions));
    }
}
