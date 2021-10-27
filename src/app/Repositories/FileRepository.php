<?php


namespace App\Repositories;

use App\Models\File;
use App\Models\FileHistory;
use App\Services\FileService;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileRepository extends BaseRepository
{
    public function getModel()
    {
        return File::class;
    }

    public function findByIds($fileIds, $documentId)
    {
        return $this->model->whereIn('id', $fileIds)->whereHas('document', function ($q) use ($documentId) {
            $q->where('document_id', $documentId);
        })->get();
    }

    public function saveDocumentFiles($filesInfo, $document)
    {
        $dataFileToSave = $fileIdsUpdated = $fileIdsDeleted = [];
        foreach ($filesInfo as $fileInfo) {
            if (isset($fileInfo['is_delete']) && $fileInfo['is_delete']) {
                $fileIdsDeleted[] = $fileInfo['id'];
                continue;
            } elseif (!is_file($fileInfo['file'])) {
                continue;
            } else {
                $file = $fileInfo['file'];
                $fileService = new FileService();
                $folderName = Config::get('constants.prefix_document') . $document->id;
                $urlOnS3 = $fileService->uploadFile($folderName, $file);
                $extension = strtolower($file->getClientOriginalExtension());
                $data = [
                    'document_id' => $document->id,
                    'file_format' => Config::get('constants.file_format')[$extension],
                    'original_name' => $file->getClientOriginalName(),
                    'url' => $urlOnS3,
                    'size' => $file->getSize(),
                ];
                if (isset($fileInfo['id']) && !empty($fileInfo['id'])) {
                    $fileIdsUpdated[] = $fileInfo['id'];
                    $dataFileToSave['update'][$fileInfo['id']] = $data;
                } else {
                    $dataFileToSave['insert'][] = $data;
                }
            }
        }
        if (!empty($fileIdsUpdated)) {
            $versionOfFiles = $this->findByIds($fileIdsUpdated, $document->id)->pluck('version', 'id')->toArray();
            if (empty($versionOfFiles)) {
                throw new NotFoundHttpException(__('message.update_document_failure'));
            }
            if (!empty($dataFileToSave['update'])) {
                foreach ($dataFileToSave['update'] as $fileId => $dataSave) {
                    $dataSave['version'] = $versionOfFiles[$fileId] + 1;
                    $file = $this->update($fileId, $dataSave);
                    $file->histories()->create([
                        'user_id' => $document->owner_id,
                        'file_id' => $file->id,
                        'file_format' => $file->file_format,
                        'original_name' => $file->original_name,
                        'url' => $file->url,
                        'size' => $file->size,
                        'version' => $file->version,
                        'action' => FileHistory::ACTION_UPDATE,
                    ]);
                }
            }
        }
        if (!empty($dataFileToSave['insert'])) {
            foreach ($dataFileToSave['insert'] as $dataFile) {
                $dataFile['version'] = File::INIT_VERSION;
                $file = $this->create($dataFile);
                $file->histories()->create([
                    'user_id' => $document->owner_id,
                    'file_id' => $file->id,
                    'file_format' => $file->file_format,
                    'original_name' => $file->original_name,
                    'url' => $file->url,
                    'size' => $file->size,
                    'version' => $file->version,
                    'action' => FileHistory::ACTION_INSERT,
                ]);
            }
        }

        if (!empty($fileIdsDeleted)) {
            if (auth()->user()->isStaff() && $document->owner_id != auth()->user()->id) {
                throw new \Exception(__('message.delete_file_failure'), 403);
            }
            foreach ($fileIdsDeleted as $fileId) {
                $file = $this->findOrFail($fileId);
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
                $this->delete($fileId);
            }
        }
    }

    public function findByFilename($filename)
    {
        return $this->model->where('url', 'like', $filename . '%')->first();
    }

    public function getDocumentID($url)
    {
        return $this->model->select('document_id')
            ->whereIn('id', function ($query) use ($url) {
                $query->select('file_id')
                    ->from(with(new FileHistory)->getTable())
                    ->whereIn('url', $url);
            })->pluck('document_id')->toArray();
    }

    public function getDocumentNew($path)
    {
        return $this->model->select('document_id')
            ->whereIn('id', function ($query) use ($path) {
                $query->select('file_id')
                    ->from(with(new FileHistory)->getTable())
                    ->where('url', 'not like', '%' . $path . '%');
            })->pluck('document_id')->toArray();
    }

    public function findDeletedFile($id)
    {
        $result = $this->model->onlyTrashed()->findOrFail($id);

        return $result;
    }

    public function findByFilenameWithDocument($documentID, $filename)
    {
        return $this->model->where('document_id', $documentID)
            ->where('url', 'like', $filename . '%')
            ->first();
    }
}
