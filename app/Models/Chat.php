<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'text',
        'id_group_chat',
        'status'
    ];

    public function scopeSearchGroup($query, $searchTerm)
    {
        return $query->where('id_group_chat', $searchTerm)
            ->orWhere(\DB::raw('UPPER(text)'), 'like', "%" . Strtoupper($searchTerm) . "%");
    }
}
