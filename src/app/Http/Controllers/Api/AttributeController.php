<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttributeCollection;
use App\Repositories\AttributeRepository;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    protected $attributeRepository;

    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }
    public function index()
    {
        return responseOK(new AttributeCollection($this->attributeRepository->getList()));
    }
}
