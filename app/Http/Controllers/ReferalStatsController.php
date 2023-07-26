<?php

namespace App\Http\Controllers;

use App\Models\ReferalStats;
use Illuminate\Http\Request;
use Auth;

class ReferalStatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReferalStats  $referalStats
     * @return \Illuminate\Http\Response
     */
    public function show(ReferalStats $referalStats)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReferalStats  $referalStats
     * @return \Illuminate\Http\Response
     */
    public function edit(ReferalStats $referalStats)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReferalStats  $referalStats
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReferalStats $referalStats)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReferalStats  $referalStats
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReferalStats $referalStats)
    {
        //
    }
    
    public function referalTree(Request $request){

        $user = Auth::user();
       
        $commission_level = \DB::select('select array_length(commission_level, 1) as level from parameters');

        $level = $commission_level[0]->level;
        //$level = 20;

        $results = \DB::select("WITH RECURSIVE children AS (
            SELECT user_id, income, 0 as depth, username,
            (select count(*) from deposits where status = 'on' and user_id = $user->id) as active
            FROM referal_stats INNER JOIN users
            ON referal_stats.user_id = users.id
            WHERE user_id = $user->id
           UNION
            SELECT op.user_id, op.income, depth + 1, u.username ,
            (select count(*) from deposits where status = 'on' and user_id = op.user_id) as active

            FROM referal_stats op INNER JOIN users u
            ON op.user_id = u.id
            JOIN children c ON op.income = c.user_id where email_verified_at is not null
           )
           SELECT *
           FROM children where depth <= '$level'");

           //return $results;

        /* return $results_commission = \DB::select("WITH RECURSIVE parents AS (
            SELECT user_id, income, 0 as depth, username,0.00 as amount
            FROM referal_stats INNER JOIN users
            ON referal_stats.user_id = users.id
            WHERE user_id = $user->id
           UNION
            SELECT op.user_id, op.income, depth - 1, u.username,(case when (
                (select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric)is null then 0 
                when ((select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric)is not null 
                then (select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric end) 
                as amount
            FROM referal_stats op INNER JOIN users u
            ON op.user_id = u.id
            JOIN parents c ON op.user_id = c.income
           )
           SELECT *
           FROM parents where depth <= '$level' and depth!=0 "); */
                
        $father = $results[0];

        $user_contruction = new \stdClass();
        $depth = [];
        for ($i=1; $i <= $level; $i++) { 
            $cont = 0;
            for ($j=0; $j < count($results); $j++) { 
                if ($results[$j]->depth == $i) {
                    $cont ++;
                }
            }
            array_push( $depth, $cont);
        }

        $user_contruction->username = $father->username;
        $user_contruction->active = $father->active;
        $user_contruction->children = $this->recuersiveReferal($father,$results);
        $user_contruction->levels = $depth;

        return $user_contruction;
          
    }

    public function recuersiveReferal($user, $results){

        $auxReferalArr = [];

        $datos = array_values(array_filter($results, function($value) use($user){
            return ($value->income == $user->user_id);
        }));

        if( $datos!=[]){
            foreach ($datos as $dato) {

                $child = new \stdClass();
                $child->username = $dato->username;
                $child->active = $dato->active;
                $child->children = $this->recuersiveReferal($dato,$results);
               
                array_push( $auxReferalArr,$child);
            }
        }
        
        return  $auxReferalArr;
    }

}
