<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;//use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'body',
        'attachment',
        'date',
        'group_id',
        'status',
    ];

    public function attachments()
    {
        return $this->hasMany(\App\Models\Attachments::class);
    }

    public function scopeWordFilter($query, $searchTerm)
    {
        return $query->where(\DB::raw('UPPER(name)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere(\DB::raw('UPPER(title)'), 'like', "%" . Strtoupper($searchTerm) . "%");
    }
}
