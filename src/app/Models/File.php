<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_id', 'file_format', 'original_name', 'url', 'size', 'version'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    const INIT_VERSION = 1;
    const WORD_FORMAT = 1;
    const PDF_FORMAT = 2;
    const IMG_FORMAT = 3;

    public function document()
    {
        return $this->belongsTo('App\Models\Document');
    }

    public function histories()
    {
        return $this->hasMany('App\Models\FileHistory');
    }

    public function deletedDocument()
    {
        return $this->document()->onlyTrashed();
    }
}
