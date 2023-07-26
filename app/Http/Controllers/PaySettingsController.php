<?php

namespace App\Http\Controllers;

use App\Models\PaySettings;
use App\Models\User;
use App\Models\Type;
use App\Models\Plan;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use DB;


class PaySettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

      

        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
/*             case 'activity_amount':
                $column_name =  'amount';
            break; */
            default:
                $column_name =  $request->column_name??'id';
            break;
        }
       
        $order = $request->order??'desc';

        $paySettings =  PaySettings::wordFilter($word_filter,['name','short_name'],'created_at',$from_date,$to_date)
        ->orderBy($column_name, $order)
        ->paginate($page_numbers);
        foreach ($paySettings as $paySetting) {
            $paySetting->actions = json_decode(json_encode($paySetting),true);
        }
            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $paySettings
            ]);
    }

    public function pay(){

        $paySettings = PaySettings::where('status', true)->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente.',
            'data' => $paySettings
        ]);
        
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
            'name' => 'required|unique:pay_settings',
            'short_name' => 'required|unique:pay_settings',
        ]);

        /* $users = User::get();
        return $users; */
        
        $paySettings = new PaySettings();
        $paySettings->name = $request->name;
        $paySettings->short_name = $request->short_name;

        /* agregador nuevos */
        $paySettings->deposit_fee = $request->deposit_fee;
        $paySettings->withdraw_min = $request->withdraw_min;
        $paySettings->withdraw_max = $request->withdraw_max;
        $paySettings->withdraw_fee = $request->withdraw_fee;
        $paySettings->pay_type = $request->type;
        

        if($request->hasFile('icon')){
            $image = $request->file('icon');
            $image_name = $image->getClientOriginalName();
            $image->move(public_path('/images/'),$image_name);
            $image_path = "/images/". $image_name;
            $paySettings->icon = url('').$image_path;
        }
        //$paySettings->icon = $request->icon;
        $paySettings->status = true;
        
        if ($paySettings->save()){

            $users = User::get();
            
            foreach ($users as $user) {
                
                $userWallet = new UserWallet();
                $userWallet->user_id = $user->id;
                $userWallet->pay_settings_id = $paySettings->id;
                $userWallet->status =  $request->status;
                $userWallet->save();

            }

            return response()->json([
                'success' => true,
                'data' => $paySettings->toArray()
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Pay Settings not added'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaySettings  $paySettings
     * @return \Illuminate\Http\Response
     */
    public function show(PaySettings $paySettings, $id)
    {
        $paySettings = PaySettings::find($id);
 
        if (!$paySettings) {
            return response()->json([
                'success' => false,
                'message' => 'PaySettings not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $paySettings->toArray()
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaySettings  $paySettings
     * @return \Illuminate\Http\Response
     */
    public function edit(PaySettings $paySettings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaySettings  $paySettings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $paySettings = PaySettings::findOrFail($request->id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 403);
        }

        //$user->update($request->all());

        $this->validate($request, [
            'name' => 'required',
            'short_name' => 'required',
        ]);

        /* $users = User::get();
        return $users; */
        

        $paySettings->name = $request->name;
        $paySettings->short_name = $request->short_name;

        /* agregador nuevos */
        $paySettings->deposit_fee = $request->deposit_fee;
        $paySettings->withdraw_min = $request->withdraw_min;
        $paySettings->withdraw_max = $request->withdraw_max;
        $paySettings->withdraw_fee = $request->withdraw_fee;
        $paySettings->pay_type = $request->type;



        if($request->hasFile('icon')){
            $image = $request->file('icon');
            $image_name = $image->getClientOriginalName();
            $image->move(public_path('/images/'),$image_name);
            $image_path = "/images/". $image_name;
            $paySettings->icon = url('').$image_path;
        }
        //$paySettings->icon = $request->icon;
        $paySettings->status =  $request->status;


        

        $paySettings->save();

        return response()->json(['message' => 'Usuario actualizado con exito.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaySettings  $paySettings
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pay_setting = PaySettings::find($id);
 
        if (!$pay_setting) {
            return response()->json([
                'success' => false,
                'message' => 'PaySettings not found'
            ], 400);
        }
 
        if ($pay_setting->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'data' => $pay_setting
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'PaySettings can not be deleted'
            ], 500);
        }
    }
}
