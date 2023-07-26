<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends MasterModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'path',
        'img_post',
        'date',
        'status',
    ];

  /*   public function scopeWordFilter($query, $searchTerm)
    {
        return $query->where(\DB::raw('UPPER(title)'), 'like', "%" . Strtoupper($searchTerm) . "%");
    } */

}
