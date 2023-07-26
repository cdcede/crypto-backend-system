<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class User extends Authenticatable 
{
    use HasFactory, Notifiable, HasApiTokens,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'gender',
        'email',
        'activation_code',
        'password',
        'cell_phone',
        'country',
        'state',
        'city',
        'activated',
        'secret_key',
        'verify',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'ip_reg',
        'last_access_time',
        'last_access_ip',
        'lang',
        'verified',
        'cp_next_login',
        'tour',
        'phone_activation_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'activation_code',
        'secret_key',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /* protected $casts = [
        'email_verified_at' => 'datetime',
    ]; */

    public function role() {
        return $this->hasOne(\App\Models\Role::class);
    }

    public function loginSecurity()
    {
        return $this->hasOne(\App\Models\LoginSecurity::class);
    }

    public function isNotActivated()
    {
        return $this->activated ? false : true;
    }

    public function userGroups()
    {
        return $this->hasMany(\App\Models\UserGroups::class);
    }
    public function scopeWordFilter($query, $searchTerm, $variables = [],$created_at=null,$from_date= null,$to_date= null)
    {
        $i = 0;
        foreach ($variables as $v) {
            ($i==0)?$query->where(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%")
            :$query->orWhere(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%");
            $i++;
        }
        if($from_date!=null){
            $query->whereBetween($created_at,[$from_date.' 00:00:00',$to_date.' 23:59:59']);
        }


       /*  return $query->where(DB::raw('UPPER(name)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere(DB::raw('UPPER(title)'), 'like', "%" . Strtoupper($searchTerm) . "%"); */
    }
   /*  public function scopeWordFilter($query, $searchTerm)
    {
        return $query->where(\DB::raw('UPPER(username)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere('first_name', 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere('last_name', 'like', "%" . Strtoupper($searchTerm) . "%");
    } */
}
