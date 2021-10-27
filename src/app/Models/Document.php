<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'owner_id',
        'folder_id',
        'name',
        'document_type_id',
        'document_object_id',
        'service_user_id',
        'partner_name',
        'version'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */

    const CODE_EXIT_DOCTYPE = 469;

    protected $table = 'documents';

    public function store()
    {
        return $this->belongsTo('App\Models\Store', 'store_id')->with('office.division.branch');
    }

    public function basicStore()
    {
        return $this->belongsTo('App\Models\Store', 'store_id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function documentType()
    {
        return $this->belongsTo('App\Models\DocumentType');
    }

    public function serviceUser()
    {
        return $this->belongsTo('App\Models\ServiceUser');
    }

    public function attributes()
    {
        return $this->belongsToMany(
            'App\Models\Attribute',
            'document_attributes',
            'document_id',
            'attribute_id'
        )->withPivot(['value', 'start_date', 'end_date'])->withTimestamps();
    }

    public function attributeMails()
    {
        return $this->belongsToMany(
            'App\Models\Attribute',
            'document_attributes',
            'document_id',
            'attribute_id'
        )->where('code', Attribute::VALIDITY_PERIOD)
            ->withPivot(['value', 'start_date', 'end_date'])
            ->wherePivot('end_date', '<>', null);
    }

    public function tags()
    {
        return $this->belongsToMany(
            'App\Models\Tag',
            'document_tag',
            'document_id',
            'tag_id'
        )->withTimestamps();
    }

    public function documentTag()
    {
        return $this->hasMany('App\Models\DocumentTag');
    }

    public function files()
    {
        return $this->hasMany('App\Models\File');
    }

    // relation use in tree folder
    public function children()
    {
        return $this->hasMany('App\Models\File');
    }

    public function folder()
    {
        return $this->belongsTo('App\Models\Folder');
    }

    public function storePermission()
    {
        return $this->belongsToMany(
            'App\Models\Store',
            'document_permission',
            'document_id',
            'store_id'
        )->withTimestamps();
    }

    public function documentObject()
    {
        return $this->belongsTo('App\Models\DocumentObject');
    }

    public function scopeSearchName($query, $keywordFullwidth, $keywordHalfwidth)
    {
        return $query->where('name', 'like', '%' . $keywordFullwidth . '%')
            ->orWhere('name', 'like', '%' . $keywordHalfwidth . '%');
    }

    public function scopeSearchPartnerName($query, $keywordFullwidth, $keywordHalfwidth)
    {
        return $query->orWhere('partner_name', 'like', '%' . $keywordFullwidth . '%')
            ->orWhere('partner_name', 'like', '%' . $keywordHalfwidth . '%');
    }

    public function scopeSearchOriginalName($query, $keywordFullwidth, $keywordHalfwidth)
    {
        return $query->orWhereIn('id', function ($sql) use ($keywordFullwidth, $keywordHalfwidth) {
            $sql->select('document_id')
                ->from(with(new File)->getTable())
                ->where('original_name', 'like', '%' . $keywordFullwidth . '%')
                ->orWhere('original_name', 'like', '%' . $keywordHalfwidth . '%');
        });
    }

    public function scopeSearchFileName($query, $keywordFullwidth, $keywordHalfwidth)
    {
        return $query->orWhereIn('service_user_id', function ($sql) use ($keywordFullwidth, $keywordHalfwidth) {
            $sql->select('id')
                ->from(with(new ServiceUser)->getTable())
                ->where('name', 'like', '%' . $keywordFullwidth . '%')
                ->orWhere('name', 'like', '%' . $keywordHalfwidth . '%');
        });
    }

    public function scopeSearchTagName($query, $tags)
    {
        return $query->whereIn('id', function ($sql) use ($tags) {
            $sql->select('document_id')
                ->from(with(new DocumentTag)->getTable())
                ->whereIn('tag_id', $tags);
        });
    }

    public function scopePermission($query, $storeId, $positionID)
    {
        //get document has permission
        $documentIDPerssion = DocumentPermission::where('store_id', $storeId)
            ->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . $positionID . ',%')
            ->pluck('document_id')
            ->toArray();

        //get service user has permission view
        $serviceUserIDS = Document::whereIn('id', $documentIDPerssion)
            ->whereNotNull('service_user_id')
            ->pluck('service_user_id')
            ->toArray();

        //get all document with service user
        $documentIDS = Document::whereIn('service_user_id', $serviceUserIDS)
            ->pluck('id')
            ->toArray();


        $documentIDS = array_merge($documentIDS, $documentIDPerssion);
        $documentIDS = array_values(array_unique($documentIDS));
        return $query->whereIn('id', $documentIDS);
    }

    public function scopeContractDate($query, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return $query->whereIn('id', function ($sql) use ($startDate, $endDate) {
                $sql->select('document_id')
                    ->from(with(new DocumentAttribute)->getTable())
                    ->where('attribute_id', function ($q) {
                        $q->select('id')
                            ->from(with(new Attribute)->getTable())
                            ->where('code', Attribute::EXECUTION_DATE);
                    })
                    ->whereDate('start_date', '>=', $startDate)
                    ->whereDate('start_date', '<=', $endDate);
            });
        }
        if ($startDate) {
            return $query->whereIn('id', function ($sql) use ($startDate) {
                $sql->select('document_id')
                    ->from(with(new DocumentAttribute)->getTable())
                    ->where('attribute_id', function ($q) {
                        $q->select('id')
                            ->from(with(new Attribute)->getTable())
                            ->where('code', Attribute::EXECUTION_DATE);
                    })
                    ->whereDate('start_date', '>=', $startDate);
            });
        }
        if ($endDate) {
            return $query->whereIn('id', function ($sql) use ($endDate) {
                $sql->select('document_id')
                    ->from(with(new DocumentAttribute)->getTable())
                    ->where('attribute_id', function ($q) {
                        $q->select('id')
                            ->from(with(new Attribute)->getTable())
                            ->where('code', Attribute::EXECUTION_DATE);
                    })
                    ->whereDate('start_date', '<=', $endDate);
            });
        }

    }

    public function scopeDocumentExit($query, $documentObjectId, $serviceUserId)
    {
        return $query->where('document_object_id', $documentObjectId)
            ->where('service_user_id', $serviceUserId);
    }

    public function documentPermission()
    {
        return $this->hasMany('App\Models\DocumentPermission');
    }

    public function documentPermissionUserStaff()
    {
        return $this->hasMany('App\Models\DocumentPermission')
            ->where('store_id', auth()->user()->store_id)
            ->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . auth()->user()->position_id . ',%');
    }

    public function mailDocument()
    {
        return $this->hasOne('App\Models\MailDocument');
    }

    public function scopeGetDeletedFile($query)
    {
        return $query
            ->select('documents.*', DB::raw('(select max(deleted_at) from files where documents.id = files.document_id) as max_deleted_at'))
            ->withTrashed()
            ->whereIn('id', function ($sql) {
                $sql->select('document_id')
                    ->from(with(new File)->getTable())
                    ->where('deleted_at', '<>', null);
            })->orderBy('max_deleted_at', 'desc');
    }
}
