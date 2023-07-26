<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'q_days',
        'min_deposit',
        'max_deposit',
        'percent',
        'period',
        'days',
        'deposit_fee',
        'withdraw_fee',
        'image',
        'icon',
        'custom_amount',
        'status',
        'withdrawel_mondly',
        'return_capital',
        'rol_invertion',
    ];
}
