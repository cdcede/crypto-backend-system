<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use Auth;
use DB;
use Storage;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $plans = Plan::where('status','=', 'on')->get();
        $user = Auth::user();
        
        $plans_send = [];
        foreach($plans as $plan){

            
            $array_invertion = mb_split(',', mb_substr($plan->rol_invertion, 1, -1));
            $PASS = in_array($user->role()->first()->role, $array_invertion); 
            if($PASS){
                array_push($plans_send,$plan);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $plans_send
        ]);
    }
    public function plans_user(Request $request)
    {
        $plans = Plan::where('status','=', 'on')->get();
        $user = Auth::user();
        
        $plans_send = [];
        foreach($plans as $plan){
            $plan->image = 'https://api.wagedollar.io/'.$plan->image;
            $plan->icon = 'https://api.wagedollar.io/'.$plan->icon;
            /* $plan->image = 'https://192.168.7.81:8000/'.$plan->image;
            $plan->icon = 'http://192.168.7.81:8000/'.$plan->icon; */
            $array_invertion = mb_split(',', mb_substr($plan->rol_invertion, 1, -1));
            $PASS = in_array($user->role()->first()->role, $array_invertion); 
            if($PASS){
                array_push($plans_send,$plan);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $plans_send
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
            'name' => 'required | unique:plans',
            'description' => 'required',
            'q_days' => 'required',
            'min_deposit' => 'required',
            'max_deposit' => 'required',
            'percent' => 'required',
            'period' => 'required',
            'days' => 'required',
            'withdrawel_mondly' => 'required',
            'return_capital' => 'required',
            'rol_invertion' => 'required',
            //'deposit_fee' => 'required',
            //'withdraw_fee' => 'required',
            //'image' => 'required',
            //'icon' => 'required',
            //'custom_amount' => 'required',
        ]);

        $plan = new Plan();
        $plan->name = $request->name;
        $plan->description = $request->description;
        $plan->q_days = $request->q_days;
        $plan->min_deposit = $request->min_deposit;
        $plan->max_deposit = $request->max_deposit;
        $plan->percent = $request->percent;
        $plan->period = $request->period;
        $plan->days = $request->days;

        $plan->withdrawel_mondly = $request->withdrawel_mondly;
        $plan->return_capital = $request->return_capital;
        $plan->rol_invertion = $request->rol_invertion;

        $plan->deposit_fee = $request->deposit_fee;
        $plan->withdraw_fee = $request->withdraw_fee;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $image_name =  time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('/images/packages/'.str_replace(" ","-",$request->name)),$image_name);
            $image_path = "images/packages/".str_replace(" ","-",$request->name)."/" . $image_name;
            $plan->image = $image_path;
          /*   $plan->image = 'https://api.wagedollar.io/'.$image_path; */
        }

        if($request->hasFile('icon')){
            $icon = $request->file('icon');
            $icon_name =  time().'.'.$icon->getClientOriginalExtension();
            $icon->move(public_path('/images/packages/'.str_replace(" ","-",$request->name)),$icon_name);
            $icon_path = "images/packages/".str_replace(" ","-",$request->name)."/" . $icon_name;
         /*    $plan->icon = 'https://api.wagedollar.io/'.$icon_path; */
         $plan->icon = $icon_path;
        }else{
            $plan->icon = null;
        }

        $plan->custom_amount = $request->custom_amount;
        $plan->status = 'on';
 
        if ($plan->save())
            return response()->json([
                'success' => true,
                'message' => 'Plan guardado correctamente.',
                'data' => $plan
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Plan not added'
            ], 500);
    }


    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $plan = Plan::find($id);
 
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $plan->toArray()
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $plan = Plan::find($request->id);
 
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 400);
        }

        $plan->name = $request->name;
        $plan->description = $request->description;
        $plan->q_days = $request->q_days;
        $plan->min_deposit = $request->min_deposit;
        $plan->max_deposit = $request->max_deposit;
        $plan->percent = $request->percent;
        $plan->period = $request->period;
        $plan->days = $request->days;
        $plan->deposit_fee = $request->deposit_fee;
        $plan->withdraw_fee = $request->withdraw_fee;

        $plan->withdrawel_mondly = $request->withdrawel_mondly;
        $plan->return_capital = $request->return_capital;
        $plan->rol_invertion = $request->rol_invertion;
        $plan->status = $request->status=='true'?'on':'off';
         
        //$plan->image = $request->image;
        //$plan->icon = $request->icon;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('/images/packages/'.str_replace(" ","-",$request->name)),$image_name);
            $image_path = "images/packages/".str_replace(" ","-",$request->name)."/" . $image_name;
            $plan->image = $image_path;
           /*  $plan->image = url('').$image_path; */
        }

        if($request->hasFile('icon')){
            $icon = $request->file('icon');
            $icon_name = time().'.'.$icon->getClientOriginalExtension();
            $icon->move(public_path('/images/packages/'.str_replace(" ","-",$request->name)),$icon_name);
            $icon_path = "images/packages/".str_replace(" ","-",$request->name)."/" . $icon_name;
          /*   $plan->icon = 'https://api.wagedollar.io'.$icon_path; */
          $plan->image = $image_path;
        }else{
            $plan->icon = null;
        }
        $plan->custom_amount = $request->custom_amount;

 
        if ($plan->save())
            return response()->json([
                'success' => true,
                'message' => 'Plan actualizado correctamente.',
                'data' => $plan
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Plan can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plan = Plan::find($id);
 
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 400);
        }
 
        if ($plan->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Plan eliminado correctamente.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Plan can not be deleted'
            ], 500);
        }
    }


    public function listplan(Request $request)
    {
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $order = $request->order??'desc';
        $column_name = $request->column_name??'id';
     /*    switch ($request->column_name) {
            case 'movements':
                $column_name =  'description';
            break;
            case 'activity_amount':
                $column_name =  'amount';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }
 */
        $myObj= new \stdClass();
        if($from_date!=null){
            $plans = Plan::whereBetween('created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->where(function($query) use($word_filter){
                $query ->where(DB::raw('UPPER(name)'), 'like', "%" . Strtoupper($word_filter) . "%")
                ->orwhere(DB::raw('UPPER(description)'), 'like', "%" . Strtoupper($word_filter) . "%");
                 })
                    ->orderBy($column_name, $order)
                ->paginate($page_numbers);
                foreach ($plans as $plan) {


                    $plan->actions = json_decode(json_encode($plan),true);
            }

        }else{
            $plans = Plan::where(DB::raw('UPPER(name)'), 'like', "%" . Strtoupper($word_filter) . "%")
                    ->orwhere(DB::raw('UPPER(description)'), 'like', "%" . Strtoupper($word_filter) . "%")
                    ->orderBy($column_name, $order)
                ->paginate($page_numbers);
                 foreach ($plans as $plan) {

                    $plan->actions = json_decode(json_encode($plan),true);
            }
        }
        
 
        if ($plans) {
            return response()->json([
                'success' => true,
                'message' => 'Planes obtenidos correctamente.',
                'data' => $plans
            ]);
        }

    }


    public function getImagesPlan(Request $request)
    {
        $plan = Plan::find($request->id);
        $images = new \stdClass();



        /* $base64Image = file_get_contents($plan->image, false, stream_context_create(['http' => ['ignore_errors' => true]]));
        $base64Image = base64_encode($base64Image);
        $image->image = 'data:'.mime_content_type($base64Image) . ';base64,' . $base64Image;

        $base64Icon = file_get_contents($plan->icon, false,  stream_context_create(['http' => ['ignore_errors' => true]]));
        $base64Icon = base64_encode(($base64Icon));
        $image->icon = 'data:'.mime_content_type($base64Icon) . ';base64,' . $base64Icon;
        */
        if($plan->image!=null){
        $path = public_path($plan->image);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $images->image = $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        if($plan->icon!=null){
            $path = public_path($plan->icon);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $images->icon = $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        


        if ($plan) {
            return response()->json([
                'success' => false,
                'message' => 'Imagenes obtenidas correctamente.',
                'data' => $images
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 400);
        }
    }
}
