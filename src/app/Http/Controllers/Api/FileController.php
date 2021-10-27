<?php

namespace App\Http\Controllers\Api;

use App\Helper\Constant;
use App\Http\Controllers\Controller;
use App\Http\Resources\FileHistoryCollection;
use App\Models\File;
use App\Models\FileHistory;
use App\Models\Role;
use App\Repositories\FileHistoryRepository;
use App\Repositories\FileRepository;
use App\Repositories\FileSetManagementRepository;
use App\Repositories\FolderRepository;
use App\Repositories\RoleRepository;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $fileRepository;
    protected $fileHistoryRepository;
    protected $roleRepository;
    protected $fileService;
    protected $fileSetManagementRepository;
    protected $folderRepository;

    public function __construct(
        FileRepository $fileRepository,
        FileHistoryRepository $fileHistoryRepository,
        RoleRepository $roleRepository,
        FileService $fileService,
        FileSetManagementRepository $fileSetManagementRepository,
        FolderRepository $folderRepository
    )
    {
        $this->fileRepository = $fileRepository;
        $this->fileHistoryRepository = $fileHistoryRepository;
        $this->roleRepository = $roleRepository;
        $this->fileService = $fileService;
        $this->fileSetManagementRepository = $fileSetManagementRepository;
        $this->folderRepository = $folderRepository;
    }

    public function fileVersions($fileId)
    {
        $versions = $this->fileHistoryRepository->getAllVersions($fileId);
        return responseOK(new FileHistoryCollection($versions));
    }

    public function preview($id)
    {
        $file = $this->fileRepository->findOrFail($id);

        // preview file is not doc/docx
        if ($file->file_format != File::WORD_FORMAT) {
            $url = $this->fileService->signAPrivateDistribution($file->url);
            return responseOK(['url' => $url]);
        }
        return responseError(403, __('message.preview_document_failure'));
    }

    public function download($id, Request $request)
    {
        if ($request->is_deleted) {
            $file = $this->fileRepository->findDeletedFile($id);
        } else {
            $file = $this->fileRepository->findOrFail($id);
        }
        return $this->fileService->downloadFile($file);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $file = $this->fileRepository->find($id);
            if (auth()->user()->isStaff() && $file->document->owner_id != auth()->user()->id) {
                return responseError(403, __('message.delete_file_failure'));
            }
            $file->histories()->create([
                'user_id' => auth()->user()->id,
                'file_id' => $file->id,
                'file_format' => $file->file_format,
                'original_name' => $file->original_name,
                'url' => $file->url,
                'size' => $file->size,
                'version' => $file->version,
                'action' => FileHistory::ACTION_DELETE,
            ]);
            $this->fileRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $file = $this->fileRepository->findDeletedFile($id);
            $belongToDocument = $file->document()->withTrashed()->first();
            if (auth()->user()->isStaff() && $belongToDocument->owner_id != auth()->user()->id) {
                return responseError(403, __('message.restore_file_failure'));
            }
            // write log revert file
            $file->histories()->create([
                'user_id' => auth()->user()->id,
                'file_id' => $file->id,
                'file_format' => $file->file_format,
                'original_name' => $file->original_name,
                'url' => $file->url,
                'size' => $file->size,
                'version' => $file->version,
                'action' => FileHistory::ACTION_REVERT,
            ]);
            // restore when doc belong to a fileset
            if (!empty($belongToDocument->service_user_id)) {
                $belongToDocument->serviceUser()->withTrashed()->restore();
                $belongToDocument->folder()->withTrashed()->restore();
                foreach ($belongToDocument->folder->parent()->withTrashed()->get() as $parent) {
                    if ($parent->trashed()) {
                        $parent->restore();
                    }
                }
                $fileSetManagement = $this->fileSetManagementRepository->filterWithTrashed([
                    'service_user_id' => $belongToDocument->serviceUser->id,
                    'document_type_id' => $belongToDocument->document_type_id,
                    'store_id' => $belongToDocument->store_id
                ]);
                if ($fileSetManagement) {
                    $fileSetManagement->restore();
                }
            }
            $belongToDocument->mailDocument()->withTrashed()->restore();
            $belongToDocument->restore();
            $file->restore();
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function deletePermanently($id)
    {
        DB::beginTransaction();
        try {
            $file = $this->fileRepository->findDeletedFile($id);
            $belongToDocument = $file->document()->withTrashed()->first();
            if (auth()->user()->isStaff() && $belongToDocument->owner_id != auth()->user()->id) {
                return responseError(403, __('message.restore_file_failure'));
            }
            $this->fileService->deleteFile($file->url);
            $file->forceDelete();
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }
}
