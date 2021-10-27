<?php


namespace App\Repositories;


use App\Models\FileHistory;
use Illuminate\Support\Carbon;

class FileHistoryRepository extends BaseRepository
{
    public function getModel()
    {
        return FileHistory::class;
    }

    public function getAllVersions($fileId)
    {
        return $this->model->where('file_id', $fileId)
            ->whereIn('action', [FileHistory::ACTION_INSERT, FileHistory::ACTION_UPDATE])
            ->orderBy('version')
            ->get();
    }

    public function getFileWithVersion($fileID, $version)
    {
        return $this->model->where('file_id', $fileID)
            ->where('version', $version)
            ->first();
    }

    public function saveFileHistories($files)
    {
        foreach ($files as $file) {
            $fileHistory[] = [
                'user_id' => auth()->user()->id,
                'file_id' => $file->id,
                'file_format' => $file->file_format,
                'original_name' => $file->original_name,
                'url' => $file->url,
                'size' => $file->size,
                'version' => $file->version,
                'action' => FileHistory::ACTION_DELETE,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (!empty($fileHistory)) {
            $this->insertMany($fileHistory);
        }
    }
}
