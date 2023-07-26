<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletsLogger extends MasterModel
{
    use HasFactory;//, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'pay_settings_id',
        'wallet',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function pay()
    {
        return $this->belongsTo(\App\Models\PaySettings::class,'pay_settings_id');
    }

   /*  public function scopeWordFilter($query, $searchTerm)
    {
        return $query->where(\DB::raw('UPPER(wallet)'), 'LIKE', '%' .Strtoupper($searchTerm) . '%')
            ->orWhere(\DB::raw('UPPER(users.username)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere(\DB::raw('UPPER(users.email)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere(\DB::raw('UPPER(pay_settings.name)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->join('users', 'users.id', '=', 'wallets_loggers.user_id')
            ->join('pay_settings', 'pay_settings.id', '=', 'wallets_loggers.pay_settings_id');

    } */

}
