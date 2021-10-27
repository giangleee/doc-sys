<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTypeObject extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_type_id', 'document_object_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_type_object';
}
