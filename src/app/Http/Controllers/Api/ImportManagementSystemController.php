<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportManagementSystemRequest;
use App\Imports\ManagementSystemImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportManagementSystemController extends Controller
{
    public function import(ImportManagementSystemRequest $request)
    {
        $import = new ManagementSystemImport;
        Excel::import($import, $request->file);
        return responseOK($import->response);
    }
}
