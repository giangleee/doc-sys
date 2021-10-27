<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'avatar', 'full_name', 'katakana_name', 'phone'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'profiles';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getAvatarAttribute($fileName)
    {
        $fileService = new FileService();
        $expiresIn = config('session.lifetime') * 60;
        return !empty($fileName) ? $fileService->signAPrivateDistribution($fileName, $expiresIn) : null;
    }
}
