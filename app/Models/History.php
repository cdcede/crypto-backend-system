<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends MasterModel
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'deposit_id',
        'amount',
        'type',
        'description',
        'actual_amount',
    ];

    public function scopeTransaction($query, $searchTerm)
    {
        return $query->where('type', 'like', "%" . $searchTerm . "%");
    }
}
