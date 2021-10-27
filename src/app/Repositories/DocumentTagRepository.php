<?php


namespace App\Repositories;

use App\Models\DocumentTag;

class DocumentTagRepository extends BaseRepository
{
    public function getModel()
    {
        return DocumentTag::class;
    }
}
