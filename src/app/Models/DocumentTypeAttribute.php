<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTypeAttribute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_id', 'document_type_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_type_attribute';
}
