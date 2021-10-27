<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_id', 'tag_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_tag';
}
