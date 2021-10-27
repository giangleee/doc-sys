<?php


namespace App\Http\Resources;


use App\Repositories\DocumentRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TreeFolderCollection extends ResourceCollection
{
    public $collects = TreeFolderResource::class;

    public function toArray($request)
    {
        $documentRepository = new DocumentRepository();
        $freeDocument = $documentRepository->getAllFreeDocument();
        return [
            'folders' => $this->collection,
            'free_documents' => new FreeDocumentCollection($freeDocument)
        ];
    }
}
