<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use HasFactory;//use HasFactory, SoftDeletes;

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
        'period',
        'status',
        'return_profit',
        'return_profit_percent',
        'percent',
        'pay_to_egold_directly',
        'use_compound',
        'work_week',
        'parent',
        'withdraw_principal',
        'withdraw_principal_percent',
        'withdraw_principal_duration',
        'compound_min_deposit',
        'compound_max_deposit',
        'compound_percents_type',
        'compound_min_percent',
        'compound_max_percent',
        'compound_percents',
        'closed',
        'withdraw_principal_duration_max',
        'dsc',
        'hold',
        'delay',
        'ordering',
        'deposits_limit_num',
        'rpcp',
        'ouma',
        'pax_utype',
        'dawifi',
        'pae',
        'amount_mult',
        'data',
        'rc',
        'allow_internal_deps',
        'allow_external_deps',
        's',
        'move_to_plan',
        'move_to_plan_perc',
        'compound_return',
        'power_unit',
        'power_rate',
        'order',
        'currency',
        'image',
        'icon',
        'custom_amount',
    ];

    /**
     * Get the plans for the type.
     */
    public function plans()
    {
        return $this->hasMany(\App\Models\Plan::class, 'parent');
    }
}
