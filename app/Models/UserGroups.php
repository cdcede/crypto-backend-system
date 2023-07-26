<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroups extends Model
{
    use HasFactory;//use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'group_id',
        'status',
    ];

    public function group()
    {
        return $this->belongsTo(\App\Models\Group::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeGroup($query, $searchTerm)
    {
        return $query->where('group_id', '=', $searchTerm);
    }
}
