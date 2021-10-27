<?php


namespace App\Repositories;


use App\Models\Tag;
use Illuminate\Http\Request;

class TagRepository extends BaseRepository
{
    public function getModel()
    {
        return Tag::class;
    }

    public function getList(Request $request)
    {
        return $this->model->paginate($request->limit ? $request->limit : 9999999);
    }

    public function getTagName($arrayName)
    {
        return $this->model->whereIn('name', $arrayName)->pluck('name', 'id')->toArray();
    }

    public function saveDocumentTags($tags, $document)
    {
        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }
        $document->tags()->detach();
        $tagsExisted = $this->getTagName($tags);
        $document->tags()->sync(array_keys($tagsExisted));
        $newTags = array_diff($tags, $tagsExisted);
        if (count($newTags)) {
            foreach ($newTags as $tagName) {
                $deletedTag = $this->model->withTrashed()->where('name', $tagName)->first();
                if ($deletedTag) {
                    $tag = $deletedTag;
                    $deletedTag->restore();
                } else {
                    $tag = $this->model->create([
                        'name' => $tagName
                    ]);
                }
                $document->tags()->attach($tag->id);
            }
        }
    }
}
