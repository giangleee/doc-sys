<?php


namespace App\Repositories;


use App\Models\FolderDocument;

class FolderDocumentRepository extends BaseRepository
{
    public function getModel()
    {
        return FolderDocument::class;
    }
}
