<?php

namespace App\Models;

use App\Helper\Constant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    const IS_SYSTEM = 1;
    const IS_NOT_SYSTEM = 0;
    const NAME_FOLDER_COMMON = '共通';
    const IS_COMMON = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id', 'division_id', 'office_id', 'owner_id', 'service_user_id',
        'parent_id', 'name', 'is_system', 'is_common', 'document_type_id', 'store_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'folders';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Folder', 'parent_id')->with('parent');
    }

    // get children folder for tree folder
    public function children()
    {
        if (!empty(Constant::$foldersIdToFilter)) {
            return $this->hasMany('App\Models\Folder', 'parent_id')
                ->whereIn('id', Constant::$foldersIdToFilter);
        }
        return $this->hasMany('App\Models\Folder', 'parent_id');
    }
    public function childrens()
    {
        if (!empty(Constant::$foldersIdToFilter)) {
            return $this->hasMany('App\Models\Folder', 'parent_id')
                ->whereIn('id', Constant::$foldersIdToFilter)->with('childrens');
        }
        return $this->hasMany('App\Models\Folder', 'parent_id')->with('childrens');
    }


    public function childrenFolder()
    {
        if (!empty(Constant::$foldersIdToFilter)) {
            return $this->hasMany('App\Models\Folder', 'parent_id')
                ->whereIn('id', Constant::$foldersIdToFilter)->with([
                    'childrenFolder',
                    'documents' => function ($query) {
                        $query->whereHas('documentPermissionUserStaff');
                    },
                ]);
        }
        return $this->hasMany('App\Models\Folder', 'parent_id')
            ->with([
                'childrenFolder',
                'documents' => function ($query) {
                    $query->whereHas('documentPermissionUserStaff');
                },
            ]);
    }

    public function documents()
    {
        $relation = $this->hasMany('App\Models\Document');
        if (!empty(Constant::$documentsIdToFilter)) {
            $relation->whereIn('id', Constant::$documentsIdToFilter);
        }
        if (!empty(Constant::$documentsIdNoPermission)) {
            $relation->whereNotIn('id', Constant::$documentsIdNoPermission);
        }
        return $relation->with('documentType', 'children'); // children are files
    }

    public function office()
    {
        return $this->belongsTo('App\Models\Office');
    }

    // get children folder for create/edit document
    public function folderChildren()
    {
        if (!empty(Constant::$foldersIdToFilter)) {
            return $this->hasMany('App\Models\Folder', 'parent_id')
                ->whereIn('id', Constant::$foldersIdToFilter)
                ->with('folderChildren');
        }
        return $this->hasMany('App\Models\Folder', 'parent_id')->with('folderChildren');
    }

    public function getDocumentPermission()
    {
        return $this->hasMany('App\Models\Document')->whereHas('documentPermissionUserStaff');
    }

    public function nearestParent()
    {
        return $this->belongsTo('App\Models\Folder', 'parent_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    public function division()
    {
        return $this->belongsTo('App\Models\Division');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\Store');
    }
}
