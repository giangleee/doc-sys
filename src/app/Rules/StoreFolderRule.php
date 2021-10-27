<?php

namespace App\Rules;

use App\Repositories\FolderRepository;
use App\Models\Folder;
use Illuminate\Contracts\Validation\Rule;

class StoreFolderRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $folderRepo = new FolderRepository();
        if (!empty(request()->parent_id)) {
            $folderChilds = $folderRepo->findOrFail(request()->parent_id)->children->pluck('name')->toArray();
        } else {
            $folderChilds = Folder::whereNull('parent_id')->pluck('name')->toArray();
        }
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
