<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileSetManagement extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_user_id', 'store_id', 'document_type_id', 'status_contract', 'contract_cancel_date'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_set_management';

    const  STATUS_CONTRACT_ACTIVE = 1;
    const  STATUS_CONTRACT_DISABLE = 0;

    public function storePermission()
    {
        return $this->belongsToMany(
            'App\Models\Store',
            'file_set_permission',
            'file_set_management_id',
            'store_id'
        )->withTimestamps();
    }

    public function serviceUser()
    {
        return $this->belongsTo('\App\Models\ServiceUser');
    }

    public function store()
    {
        return $this->belongsTo('\App\Models\Store', 'store_id');
    }

    public function documents()
    {
        return $this->hasMany('\App\Models\Document', 'service_user_id', 'service_user_id')
            ->with('store.office.division.branch', 'files', 'owner', 'tags');
    }

    public function documentType()
    {
        return $this->belongsTo('\App\Models\DocumentType');
    }

    public function fileSetPermission()
    {
        return $this->hasMany('\App\Models\FileSetPermission');
    }

    public function documentsWithOffice($store_id, $document_type_id)
    {
        return $this->hasMany('\App\Models\Document', 'service_user_id', 'service_user_id')
            ->with('store', 'files', 'owner', 'tags')
            ->where('store_id', $store_id)
            ->where('document_type_id', $document_type_id);
    }
}
