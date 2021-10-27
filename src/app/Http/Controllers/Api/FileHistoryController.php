<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Repositories\FileHistoryRepository;
use App\Services\FileService;
use Illuminate\Http\Request;

class FileHistoryController extends Controller
{
    protected $fileHistoryRepository;
    protected $fileService;

    public function __construct(FileHistoryRepository $fileHistoryRepository, FileService $fileService)
    {
        $this->fileHistoryRepository = $fileHistoryRepository;
        $this->fileService = $fileService;
    }

    public function downloadOldVersion($historyId)
    {
        $fileHistory = $this->fileHistoryRepository->findOrFail($historyId);
        return $this->fileService->downloadFile($fileHistory);
    }

    public function previewOldVersion($historyId)
    {
        $fileHistory = $this->fileHistoryRepository->findOrFail($historyId);

        // preview file is not doc/docx
        if ($fileHistory->file_format != File::WORD_FORMAT) {
            $url = $this->fileService->signAPrivateDistribution($fileHistory->url);
            return responseOK(['url' => $url]);
        }
        return responseError(404, __('message.preview_document_failure'));
    }
}
