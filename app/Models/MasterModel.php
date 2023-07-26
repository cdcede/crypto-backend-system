<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class MasterModel extends Model
{
    use HasFactory;//use HasFactory, SoftDeletes;

    
    public function scopeWordFilter($query, $searchTerm, $variables = [],$created_at=null,$from_date= null,$to_date= null)
    {
       

      
        $query->where(function($query) use($searchTerm,$variables){
            $i = 0;
            foreach ($variables as $v) {
                ($i==0)?$query->where(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%")
                :$query->orWhere(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%");
                $i++;
            }
        });


       
        if($from_date!=null){
            $query->whereBetween($created_at,[$from_date.' 00:00:00',$to_date.' 23:59:59']);
        }


       /*  return $query->where(DB::raw('UPPER(name)'), 'like', "%" . Strtoupper($searchTerm) . "%")
            ->orWhere(DB::raw('UPPER(title)'), 'like', "%" . Strtoupper($searchTerm) . "%"); */
    }
}
