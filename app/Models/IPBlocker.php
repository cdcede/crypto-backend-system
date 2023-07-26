<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IPBlocker extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'description',
        'status',
    ];

    public function scopeWordFilter($query, $searchTerm)
    {
        return $query->where('ip', 'like', "%" . $searchTerm . "%")
            ->orWhere('description', 'like', "%" . $searchTerm . "%");
    }
}
