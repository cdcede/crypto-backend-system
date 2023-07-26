<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\UserBalances;
use Illuminate\Http\Request;
use DB;
use Auth;

class HistoryController extends Controller
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
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function show(History $history)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function edit(History $history)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, History $history)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function destroy(History $history)
    {
        //
    }

    public function historyTableFilter(Request $request){
        
        $word_filter = $request->word_filter;
        $payment = $request->payment;
        $wheres = $request->payment;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $user = Auth::user();

        if($from_date!=null){
            $histories = History::transaction($word_filter)
            ->join('pay_settings', 'pay_settings.id','=','histories.pay_id')
            ->select('histories.*', 'pay_settings.name', 'pay_settings.icon')
            ->orderBy('id', 'desc')
            ->where('user_id', $user->id)
            ->where('name', 'like', "%" .  $payment . "%")
            ->whereBetween('histories.created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->paginate($page_numbers);

            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $histories
            ]);

        }else{
            $histories = History::transaction($word_filter)
            ->join('pay_settings', 'pay_settings.id','=','histories.pay_id')
            ->select('histories.*', 'pay_settings.name', 'pay_settings.icon')
            ->orderBy('id', 'desc')
            ->where('user_id', $user->id)
            ->where('name', 'like', "%" .  $payment . "%")
            ->paginate($page_numbers);
            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $histories
            ]);
        }
        

        return response()->json([
            'success' => false,
            'message' => 'No data'
        ],500);
        
    
    }

    public function historyTableFilterAdmin(Request $request){
        
        $word_filter = $request->word_filter;
        $payment = $request->payment;
        $page_numbers = $request->page_numbers;
        $typeTransaction = $request->typeTransaction??'';
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        switch ($request->column_name) {
            case 'movements':
                $column_name =  'description';
            break;
            case 'user':
                $column_name =  'username';
            break;
            case 'activity_amount':
                $column_name =  'amount';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }
       
        $order = $request->order??'desc';
        $myObj= new \stdClass();
        $myObj2= new \stdClass();
        if($from_date!=null){
            $histories = History::transaction($typeTransaction)
            ->join('users', 'users.id','=','histories.user_id')
            ->join('pay_settings', 'pay_settings.id','=','histories.pay_id')
            ->select('histories.*', 'users.username', 'users.first_name', 'users.last_name', 'pay_settings.name','pay_settings.icon')
            ->whereBetween('histories.created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->where(DB::raw('UPPER(users.username)'), 'like', "%" . Strtoupper($word_filter) . "%")
            ->where(DB::raw('UPPER(pay_settings.name)'), 'like', "%" . Strtoupper($payment) . "%")
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

            foreach ($histories as $historie) {
                $myObj->description= $historie->description;
                $myObj->type= $historie->type; 
                $historie->movements = json_decode(json_encode((array)$myObj),true);
                $myObj2->amount= $historie->amount;
                $myObj2->icon= $historie->icon; 
                $myObj2->name= $historie->name; 
                $historie->activity_amount = json_decode(json_encode((array)$myObj2),true);
            }

            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $histories
            ]);

        }else{
            $histories = History::transaction($typeTransaction)
            ->join('users', 'users.id','=','histories.user_id')
            ->join('pay_settings', 'pay_settings.id','=','histories.pay_id')
            ->select('histories.*', 'users.username', 'users.first_name', 'users.last_name', 'pay_settings.name','pay_settings.icon')
            ->where(DB::raw('UPPER(users.username)'), 'like', "%" . Strtoupper($word_filter) . "%")
            ->where(DB::raw('UPPER(pay_settings.name)'), 'like', "%" . Strtoupper($payment) . "%")
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

         
                foreach ($histories as $historie) {
                    $myObj->description= $historie->description;
                    $myObj->type= $historie->type; 
                    $historie->movements = json_decode(json_encode((array)$myObj),true);
                    $myObj2->amount= $historie->amount;
                    $myObj2->icon= $historie->icon; 
                    $myObj2->name= $historie->name; 
                    $historie->activity_amount = json_decode(json_encode((array)$myObj2),true);
                
            }
            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $histories
            ]);
        }
        

        return response()->json([
            'success' => false,
            'message' => 'No data'
        ],500);
        
    }

    public function cancelWithdraw(Request $request ){

        $user = Auth::user();
        $id_transaction = $request->id;
        
        $historie = History::
        where('type','=','Withdraw_pending')
        ->where('user_id','=',$user->id)
        ->where('id','=',$id_transaction)->first();

       /*  $userbalance = UserBalances::
        where('type','=','Withdraw_pending')
        ->where('user_id','=',$user->id)
        ->where('id','=',$id_transaction)->first(); */

        if($historie){
            if( $historie->delete()){

                return response()->json([
                    'success' => true,
                    'message' => 'Retiro cancelado correctamente.',
                ]);
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'No se puedo eliminar el retiro.',
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No data'
            ],403);
        }
        
    }




    public function makeConversion(Request $request){

        $user = Auth::user();
/* 
        $validate_referrals_number = DB::table('referal_stats')
        ->join('users', 'users.id', '=', 'referal_stats.user_id')
        ->select(DB::raw('count(username) as referrals'))
        ->where('income', $user->id)
        ->where('activated', true)
        ->get();

        $total_referrals = $validate_referrals_number[0]->referrals;

        if ($total_referrals >= 10) { */

            $debit_amount = $request->debit_amount;
            $accredit_amount = $request->accredit_amount;

            $debit_crypto = $request->debit_crypto;
            $accredit_crypto = $request->accredit_crypto;

            $insert_debit_amount = new History();
            $insert_debit_amount->user_id = $user->id;
            $insert_debit_amount->deposit_id = 0;
            $insert_debit_amount->amount = -($debit_amount);
            $insert_debit_amount->type = 'Debit_conversion';
            $insert_debit_amount->description = 'Conversion done';
            $insert_debit_amount->actual_amount = -($debit_amount);
            $insert_debit_amount->pay_id = $debit_crypto;
            $insert_debit_amount->save();

            $insert_accredit_amount = new History();
            $insert_accredit_amount->user_id = $user->id;
            $insert_accredit_amount->deposit_id = 0;
            $insert_accredit_amount->amount = ($debit_amount)-($debit_amount*0.1);
            $insert_accredit_amount->type = 'Accredit_conversion';
            $insert_accredit_amount->description = 'Conversion done';
            $insert_accredit_amount->actual_amount = ($debit_amount)-($debit_amount*0.1);
            $insert_accredit_amount->pay_id = $accredit_crypto;
            $insert_accredit_amount->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Conversion realizada correctamente',
                'data' => [$insert_debit_amount,$insert_accredit_amount]
            ]);

   /*      }else {

            return response()->json([
                'success' => true,
                'errors' => $message = ['No cuentas con los 10 referidos activos directos para poder realizar el proceso.'],
                'data' => $total_referrals
            ],422);
        } */

    }
 
}
