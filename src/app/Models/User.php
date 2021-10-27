<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    const PRE_PASSWORD = 'yst';
    const IS_NOT_FIRST_LOGIN = 0;
    const IS_FIRST_LOGIN = 1;
    const INACTIVE = 0;
    const ACTIVE = 1;
    const IS_USER_SYSTEM_ADMIN = 1;
    const SYSTEM_ADMIN_CODE = '000000';

    protected $table = "users";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'branch_id',
        'division_id',
        'office_id',
        'store_id',
        'position_id',
        'employee_id',
        'email',
        'password',
        'is_first_login',
        'name',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Set the user's password.
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }
    public function division()
    {
        return $this->belongsTo('App\Models\Division')->with('branch');
    }
    public function office()
    {
        return $this->belongsTo('App\Models\Office')->with('division.branch');
    }
    public function store()
    {
        return $this->belongsTo('App\Models\Store')->with('office.division.branch');
    }

    public function position()
    {
        return $this->belongsTo('App\Models\Position');
    }

    public function isStaff()
    {
        return $this->role()->where('code', Role::STAFF)->exists();
    }

    public function isExecutive()
    {
        return $this->role()->where('code', Role::EXECUTIVE)->exists();
    }
}
