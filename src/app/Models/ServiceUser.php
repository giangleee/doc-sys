<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceUser extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'user_created', 'name', 'code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_users';

    public function documents()
    {
        return $this->hasMany('App\Models\Document')->with('store.office.division.branch', 'files', 'owner', 'tags');
    }

    public function fileSetManagements()
    {
        return $this->hasMany('App\Models\FileSetManagement');
    }

    public function fileSetManagement($storeId, $serviceUserId, $document_type_id = null)
    {
        $query = $this->hasOne('App\Models\FileSetManagement');
        if (is_null($document_type_id)) {
            $query->where('service_user_id', $serviceUserId)
                ->where('store_id', $storeId)->with('store', 'serviceUser');
        } else {
            $query->where('service_user_id', $serviceUserId)
                ->where('store_id', $storeId)->with('store', 'serviceUser')
                ->where('document_type_id', $document_type_id);
        }
        return $query->first();
    }

    public function fileSetManagementWithOffice($storeId, $document_type_id = null)
    {
        $query = $this->hasOne('App\Models\FileSetManagement')
            ->where('store_id', $storeId);
        if ($document_type_id) {
            $query = $query->where('document_type_id', $document_type_id);
        }

        return $query->first();
    }

    public function basicInfoDocument()
    {
        return $this->hasMany('App\Models\Document');
    }

    public function office()
    {
        return $this->belongsTo('App\Models\Office');
    }

    public function serviceUserHasDocument()
    {
        return $this->hasMany('App\Models\Document')
            ->with('documentObject', 'documentType');
    }

    public function serviceUserCreated()
    {
        return $this->belongsTo('App\Models\User', 'user_created');
    }

    public function folder()
    {
        return $this->hasMany(Folder::class, 'service_user_id');
    }

}
