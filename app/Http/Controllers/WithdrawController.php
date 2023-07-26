<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\History;
use App\Models\User;
use App\Models\PaySettings;
use App\Models\UserWallet;
use Twilio\Rest\Client;

class WithdrawController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $word_filter = $request->word_filter;
        $payment = $request->payment;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $order = $request->order??'desc';


        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

        $myObj= new \stdClass();


            $withdraw_pending = History::join('users', 'users.id' , '=', 'histories.user_id')
            ->join('pay_settings', 'pay_settings.id' , '=', 'histories.pay_id')
            ->join('user_wallets',function($join){
                $join->on('user_wallets.user_id','=','users.id')
                    ->on('user_wallets.pay_settings_id','=','pay_settings.id');
            })
            ->select('histories.*',
            DB::raw("(SELECT SUM(histories.amount) FROM histories
            WHERE (histories.type = 'Earning' 
            or  histories.type = 'Earning_released' 
            or  histories.type = 'Withdrawel'
            or  histories.type = 'Enable_commission'
            ) and  histories.user_id = users.id) as enable_amount")
            ,'users.username','users.first_name','users.last_name','users.email', 
            'pay_settings.name','pay_settings.short_name','pay_settings.icon','user_wallets.wallet as wallet_withdraw')
            ->where('histories.type', 'Withdraw_pending')
            ->wordFilter($word_filter,['username'],'histories.created_at',$from_date,$to_date)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

        
        foreach ($withdraw_pending as $wp) {
            $myObj->amount= $wp->amount;
            $myObj->icon= $wp->icon; 
            $wp->activity_amount = json_decode(json_encode((array)$myObj),true);

            $myObj= new \stdClass();
            $myObj->short_name= $wp->short_name;
            $myObj->icon= $wp->icon; 
            $wp->activity_currency = json_decode(json_encode((array)$myObj),true);
            $wp->view = json_decode(json_encode($wp,true));
        }

        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente.',
            'data' => $withdraw_pending
        ]);
    }


    public function payment_list(Request $request)
    {
        $word_filter = $request->word_filter;
        $payment = $request->payment;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $order = $request->order??'desc';


        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }
        $myObj= new \stdClass();

            $withdraw_pending = History::join('users', 'users.id' , '=', 'histories.user_id')
            ->join('pay_settings', 'pay_settings.id' , '=', 'histories.pay_id')
            ->join('user_wallets',function($join){
                $join->on('user_wallets.user_id','=','users.id')
                    ->on('user_wallets.pay_settings_id','=','pay_settings.id');
            })
            
            ->select('histories.*', 'users.username','users.first_name','users.last_name','users.email', 
            'pay_settings.name','pay_settings.short_name','pay_settings.icon','user_wallets.wallet as wallet_withdraw')
            ->where('histories.type', 'Withdrawal')
            ->wordFilter($word_filter,['username'],'histories.created_at',$from_date,$to_date)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);



        foreach ($withdraw_pending as $wp) {
            $myObj->amount= $wp->amount;
            $myObj->icon= $wp->icon; 
            $wp->activity_amount = json_decode(json_encode((array)$myObj),true);

            $myObj= new \stdClass();
            $myObj->short_name= $wp->short_name;
            $myObj->icon= $wp->icon; 
            $wp->activity_currency = json_decode(json_encode((array)$myObj),true);
            $wp->view = json_decode(json_encode($wp,true));
        }
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente.',
            'data' => $withdraw_pending]
        );
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
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function edit()
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
    public function update(Request $request)
    {
        try {
            $withdraw = History::findOrFail($request->id);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Registro no encontrado.'
            ], 403);
        }
      

        $withdraw->type = 'Withdrawal';
        $withdraw->description = 'Su pago se realizo a la billetera: '.$request->wallet;
        $withdraw->save();

        //INICIO - ENVIO DE MENSAJES POR TWILIO

        
        $user = User::where("id",$withdraw->user_id)->first();
        $coin = PaySettings::where("id",$withdraw->pay_id)->first();

        $receiverNumber = $user->cell_phone;

        if ($receiverNumber) {
            //$message = "Su pago de $".round($withdraw->amount, 2)." en ".$coin->short_name.' se ha realizado con exito desde Wage Dollar a su billetera.';
            $message = 'Hola '.$user->first_name.' '.$user->last_name.'. Su pago de $'.number_format(-$withdraw->amount, 2).' en '.$coin->short_name.' se ha realizado con exito desde Wage Dollar a su billetera.';

            try {

                $account_sid = getenv("TWILIO_SID");
                $auth_token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_number = getenv("TWILIO_NUMBER");

                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message]);



            } catch (Exception $e) {

            }


        } 
        
        return response()->json([
            'success' => true,
            'message' => 'Pago realizado con exito.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }

    public function getAmountPay(){
        {

            $user = Auth::user();

             $amountPays = DB::select("select * from pay_settings p  
             left join (
                select sum(h.amount) as amount, h.pay_id
                 from histories h  where h.user_id = $user->id and type <> 
                 'Withdraw_pending' and type <> 'Pending_commission' and type <> 'Earning_reserved' group by h.pay_id
                ) d on d.pay_id = p.id");

                $wallets = UserWallet::where('user_id', $user->id)->get();

                $histories = \DB::table('histories')
                ->where('histories.user_id',  $user->id)
                ->where('histories.type', 'Withdraw_pending')
                ->get();
                foreach ($amountPays  as $amountPay) {
                    foreach ($histories  as $historie) {
                        if($amountPay->pay_id == $historie->pay_id){
                            $amountPay->amount = $amountPay->amount + $historie->amount;
                        }
                    }
                    foreach ($wallets  as $wallet) {
                        if($amountPay->pay_id == $wallet->pay_settings_id){
                            $amountPay->wallet = $wallet->wallet;
                        }
                    }
                }

                return $amountPays;
                
            if ($user) {
                return response()->json($user);
            }

            return response()->json([
                'success' => false,
                'message' => 'No data'
            ],500);

        }
    
    }

    public function makeWithdrawal(Request $request){
        
        $user = Auth::user();
         $enable_amount = \DB::table('histories')
        ->select(DB::raw("SUM(COALESCE(histories.amount, 0)) as enable_amount"))
        ->where('user_id',$user->id)
        ->where('pay_id', $request->pay_id)
        ->where(function($query) {
            $query->where('histories.type','=','Earning')
                ->orWhere('histories.type','=','Earning_released')
                ->orWhere('histories.type','=','Withdrawel')
                ->orWhere('histories.type','=','Enable_commission');
             })->get();

   
        if ($user->verified == 3) {

            if ($request->amount >= 500 ) {
                if($request->amount <= $enable_amount[0]->enable_amount){

                    $history = new History();
                    $history->user_id = $user->id;
                    $history->deposit_id = 0;
                    $history->amount = -($request->amount);
                    $history->actual_amount = -($request->credit_amount);
                    $history->type = 'Withdraw_pending';
                    $history->description = 'withdraw pending';
                    $history->pay_id = $request->pay_id;

        
                if ($history->save())
                    return response()->json([
                        'success' => true,
                        'data' => $history->toArray()
                    ]);
                else
                    return response()->json([
                        'success' => false,
                        'message' => 'History not added'
                    ], 500);
                    
                }else{
                    return response()->json([
                        'success' => false,
                        'errors' => $message = ['Cantidad supero el limite']
                    ], 422); 
                }
            }else {
                return response()->json([
                    'success' => false,
                    'errors' => $message = ['Cantidad debe ser igual o mayor a $500']
                ], 422);
            }
        }else {
            return response()->json([
                'success' => false,
                'errors' => $message = ['Aun no has verificado tu KYC para poder realizar el retiro.']
            ], 422);
        }
       
    }

 
}