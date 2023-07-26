<?php

namespace App\Http\Controllers;

use App\Models\IPBlocker;
use Illuminate\Http\Request;

class IPBlockerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $order = $request->order??'desc';
        $page_numbers = $request->page_numbers;
        $word_filter = $request->word_filter;
        $verified = $request->verified;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

        if($from_date!=null){
             $ips = IPBlocker::wordFilter($word_filter)
            ->whereBetween('updated_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

            foreach ($ips as $ip) {
                $ip->actions = json_decode(json_encode($ip),true);
            }

            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $ips
            ]);
        }else{
            $ips =  IPBlocker::wordFilter($word_filter)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

         
            foreach ($ips as $ip) {
                $ip->actions = json_decode(json_encode($ip),true);
            }

            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $ips
            ]);
        }

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
        $this->validate($request, [
            'ip' => 'required|unique:i_p_blockers,ip|ip',
            //'description' => 'required',
            'status' => 'required'
        ]);
 
        $ip = new IPBlocker();
        $ip->ip = $request->ip;
        $ip->status = $request->status;
 
        if ($ip->save())
            return response()->json([
                'success' => true,
                'data' => $ip->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'IP not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IPBlocker  $iPBlocker
     * @return \Illuminate\Http\Response
     */
    public function show(IPBlocker $iPBlocker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IPBlocker  $iPBlocker
     * @return \Illuminate\Http\Response
     */
    public function edit(IPBlocker $iPBlocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IPBlocker  $iPBlocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IPBlocker $iPBlocker)
    {

        $this->validate($request, [
            'id' => 'required',
            'ip' => 'required|ip',
            'status' => 'required'
        ]);

        //validar misma ip

        $ip = IPBlocker::find($request->id);

        if (!$ip) {
            return response()->json([
                'success' => false,
                'message' => 'IP not found'
            ], 400);
        }

        $old_ip = IPBlocker::where('ip', $request->ip)->first();

        if (!$old_ip) {    
            $old_ip = new \stdClass();  
            $old_ip->ip = 0;
        }

        if ($request->ip == $old_ip->ip) {
            //return "si es la misma";

            if ($request->id == $old_ip->id) {
                $ip->description = $request->description;
                $ip->status = $request->status;
                if ($ip->save()) {
                    return response()->json([
                        'success' => true
                    ]);
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => 'IP can not be updated'
                    ], 500);
                }
            }else {
                return response()->json([
                    'success' => false,
                    'message' => 'IP exists'
                ], 422);
            }
            
            
            

        }else {

            //return "NO es la misma";

            $ip->ip = $request->ip;
            $ip->description = $request->description;
            $ip->status = $request->status;
            if ($ip->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nueva IP'
                ]);
            }else {
                return response()->json([
                    'success' => false,
                    'message' => 'IP can not be updated'
                ], 500);
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  \App\Models\IPBlocker  $iPBlocker
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $ip = IPBlocker::find($id);
 
        if (!$ip) {
            return response()->json([
                'success' => false,
                'message' => 'IP not found'
            ], 400);
        }
 
        if ($ip->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'IP can not be deleted'
            ], 500);
        }

    }
}
