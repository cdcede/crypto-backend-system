<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends MasterModel
{
    use HasFactory;//use HasFactory, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'w_blog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
        'img_post',
        'mini_img',
        'user_id',
        'status',
    ];

    /**
     * Get the blog for the blogcategory.
     */
    /* public function blogcategory()
    {
        return $this->hasMany(\App\Models\BlogCategory::class);
    } */
   /*  public function scopeWordFilter($query, $searchTerm)
    {
        //return $query->where('title', 'like', "%" . $searchTerm . "%");
        return $query->where(\DB::raw('UPPER(title)'), 'like', "%" . Strtoupper($searchTerm) . "%");
    } */
}
