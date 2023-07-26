<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposits extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'deposit_date',
        'last_pay_date',
        'status',
        'q_pays',
        'amount',
        'actual_amount',
        'pay_id',
        'compound',
        'dde',
        'unit_amount',
        'bonus_flag',
        'init_amount',
    ];

    
}
