<?php

namespace App\Rules;

use App\Repositories\FolderRepository;
use Illuminate\Contracts\Validation\Rule;

class UpdateFolderRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $folderRepo = new FolderRepository();
        $parentId = $folderRepo->findOrFail(request()->id)->parent_id;
        $folderChilds = $folderRepo->getFolderByParentIdExceptId($parentId, request()->id)->pluck('name')->toArray();
        return !in_array($value, $folderChilds);
    }
    /**
     * @inheritDoc
     */
    public function message()
    {
        return __('message.folder.name.unique');
    }
}
