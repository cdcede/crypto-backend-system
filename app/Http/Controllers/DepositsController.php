<?php

namespace App\Http\Controllers;

use App\Models\Deposits;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Coinbase;
use Hexters\CoinPayment\CoinPayment;
use Libern\QRCodeReader\QRCodeReader;
use DB;
use App\Models\PaySettings;
use App\Models\User;
use App\Models\Plan;

class DepositsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $deposits = Deposits::join('pay_settings', 'pay_settings.id', '=', 'deposits.pay_id')
        ->where('deposits.user_id', $user->id)->where('deposits.status', 'on')->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $deposits
        ]);
    }

    public function depositsAdmin(Request $request)
    {
        $word_filter = $request->word_filter;
        $typeTransaction = $request->typeTransaction;
        $payment = $request->payment;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $myObj = new \stdClass();


        switch ($request->column_name) {
            case 'movements':
                $column_name =  'description';
            break;
            case 'activity_amount':
                $column_name =  'name';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

        $order = $request->order??'desc';

        if($from_date!=null){


            $deposits =  DB::table('coinpayment_transactions')
            ->select('coinpayment_transactions.*', 'users.username','pay_settings.name','pay_settings.icon')
            ->join('users', 'users.email', '=', 'coinpayment_transactions.buyer_email')
            ->join('pay_settings', 'pay_settings.short_name', '=', 'coinpayment_transactions.coin')
            ->Where('username', 'like', "%" . $word_filter . "%")
            ->Where('status_text', 'like', "%" . $typeTransaction . "%")
            ->whereBetween('coinpayment_transactions.created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->orderBy( $column_name,  $order)
            ->paginate($page_numbers);

            foreach ($deposits as $deposit) {
                $myObj->amount= $deposit->amount;
                $myObj->name = $deposit->name; 
                $myObj->icon = $deposit->icon; 
                $deposit->activity_amount = json_decode(json_encode((array)$myObj),true);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Consulta ejecutada correctamente',
                'data' => $deposits
            ]);

        }else{

            $deposits =  DB::table('coinpayment_transactions')
            ->select('coinpayment_transactions.*', 'users.username','pay_settings.name','pay_settings.icon')
            ->join('users', 'users.email', '=', 'coinpayment_transactions.buyer_email')
            ->join('pay_settings', 'pay_settings.short_name', '=', 'coinpayment_transactions.coin')
            ->Where('username', 'like', "%" . $word_filter . "%")
            ->Where('status_text', 'like', "%" . $typeTransaction . "%")
            ->orderBy( $column_name,  $order)
            ->paginate($page_numbers);
     

            foreach ($deposits as $deposit) {
                $myObj->amount= $deposit->amount;
                $myObj->name = $deposit->name; 
                $myObj->icon = $deposit->icon; 
                $deposit->activity_amount = json_decode(json_encode((array)$myObj),true);
            }
    

            return response()->json([
                'success' => true,
                'message' => 'Consulta ejecutada correctamente',
                'data' => $deposits
            ]);

        }
        

        return response()->json([
            'success' => false,
            'message' => 'No data'
        ],500);
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

        $user = Auth::user();

        $this->validate($request, [
            'amount' => 'required',
            'actual_amount' => 'required'
        ]);
 
        $deposit = new Deposits();

        $deposit->user_id = $user->id;
        $deposit->plan_id = $request->plan_id;
        $deposit->deposit_date = Carbon::now();
        $deposit->last_pay_day = Carbon::now();
        $deposit->status = 'on';
        $deposit->q_pays = 0;
        $deposit->amount = $request->amount;
        $deposit->actual_amount = $request->actual_amount;
        
 
        if ($deposit->save())
            return response()->json([
                'success' => true,
                'data' => $deposit->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Deposit not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Deposits  $deposits
     * @return \Illuminate\Http\Response
     */
    public function show(Deposits $deposits)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Deposits  $deposits
     * @return \Illuminate\Http\Response
     */
    public function edit(Deposits $deposits)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Deposits  $deposits
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deposits $deposits)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Deposits  $deposits
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deposits $deposits)
    {
        //
    }

    public function coinbase(){

        /* $charge = Coinbase::createCharge([
            'name' => 'Name',
            'description' => 'Description',
            'local_price' => [
                'amount' => 100,
                'currency' => 'USD',
            ],
            'pricing_type' => 'fixed_price',
        ]); */

        /* $charges = Coinbase::getCharges();

        return $charges; */

        //return CoinPayment::getstatusbytxnid("CPFL519V2WK4C6NYPFENPHHMTC");
    }

    public function coinpayments(Request $request){

        $this->validate($request, [
            'amountTotal' => 'required',
            'note' => 'required',
            'buyer_name' => 'required',
            'buyer_email' => 'required',
            'itemDescription' => 'required',
            'itemPrice' => 'required',
            'itemSubtotalAmount' => 'required'
        ]);

        /*
        *   @required true
        */
        $user = Auth::user();
        $plan = Plan::where('name', $request->itemDescription)->first();
       
        if ($request->amountTotal < $plan->min_deposit) {
            return response()->json([
                'success' => false,
                'errors' => $message = ['No cumples con el monto minimo.']
            ],422);
        }
        if ($request->amountTotal > $plan->max_deposit) {
            return response()->json([
                'success' => false,
                'errors' => $message = ['No cumples con el monto maximo.']
            ],422);
        }
        
        $deposit_fee = DB::table('parameters')
        ->select('deposit_fee')
        ->get();

        $deposit_fee = json_decode($deposit_fee, true);
   
        $reinversion_data = DB::table('parameters')
        ->select('reinvention')
        ->get();
        $reinversion_data = json_decode($reinversion_data[0]->reinvention, true);

        if($reinversion_data['check']){

         $reinversion = DB::select(" SELECT COALESCE(SUM(childrens.amount), 0) as amount FROM (WITH RECURSIVE children AS (
            SELECT user_id, income, 0 as depth, username,
            (select COALESCE(SUM(amount), 0) from deposits where status = 'on' and user_id = 20) as amount
            FROM referal_stats INNER JOIN users
            ON referal_stats.user_id = users.id
            WHERE user_id = 20
           UNION
            SELECT op.user_id, op.income, depth + 1, u.username ,
            (select COALESCE(SUM(amount), 0)  from deposits where status = 'on' and user_id = op.user_id) as amount

            FROM referal_stats op INNER JOIN users u
            ON op.user_id = u.id
            JOIN children c ON op.income = c.user_id where email_verified_at is not null
           )
           SELECT *
           FROM children where depth = '1' ORDER BY amount desc limit ".$reinversion_data['children'].") as childrens  ");



            if($reinversion[0]->amount > $reinversion_data->min_amount){
                $transaction['reinversion'] = true; // invoice number
            }else{
                $transaction['reinversion'] = false;
            }
                        
        }else{
            $transaction['reinversion'] = false;
        }

        $fee = 3;
        
        $transaction['order_id'] = uniqid(); // invoice number
        //$transaction['amountTotal'] = (FLOAT) $request->amountTotal;
        $transaction['amountTotal'] = (FLOAT) $request->amountTotal*(1+($fee/100));
        $transaction['note'] = $request->note;
        $transaction['buyer_name'] = $request->buyer_name;
        $transaction['buyer_email'] = $request->buyer_email;
        //$transaction['token'] = $request->header('Authorization');
        $transaction['tour'] = $user->tour;
        $transaction['plan_name'] = $request->plan_name;
        $transaction['plan_id'] = $request->plan_id;
        $transaction['redirect_url'] = 'https://wagedollar.io/dashboard'; // When Transaction was comleted
        $transaction['cancel_url'] = 'https://wagedollar.io/deposits'; // When user click cancel link
        //$transaction['validate'] = $request->header('Authorization');
        
        /*
        *   @required true
        *   @example first item
        */
        
        $transaction['items'][] = [
          'itemDescription' => $request->itemDescription, //'Wage Master'
          'itemPrice' => (FLOAT) $request->itemPrice*(1+($fee/100)), // USD 37.5
          'itemQty' => (INT) 1,
          'itemSubtotalAmount' => (FLOAT) $request->itemSubtotalAmount*(1+($fee/100))// USD 37.5 
        ];
      
        $transaction['payload'] = [
          'foo' => [
              'bar' => 'baz'
          ]
        ];
        
        //return CoinPayment::generatelink($transaction);
        if($user->tour){
            $user = User::find($user->id);
            $user->tour = false;
            $user->save();
        }
        return response()->json(CoinPayment::generatelink($transaction));
    }
    
    public function getQR(Request $request){

        //return response()->json($request->qr);
        $QRCodeReader = new QRCodeReader();
        //echo $request->qr;
        $qrcode_text = $QRCodeReader->decode($request->qr);
        $text = explode("?",explode(":", $qrcode_text)[1])[0];
        //echo $text;
        return response()->json($text);
    
    }

    public function validatePayments(Request $request){
        /**
        * this is triger function for running Job proccess
        */
        return CoinPayment::getstatusbytxnid($request->txn_id);
        // output example: "Cancelled / Timed Out"
    }

    public function validateCryptoAmount(Request $request){

        $cryto_amount = DB::table('user_balances')
        ->join('pay_settings', 'pay_settings.id', '=', 'user_balances.pay_id')
        ->join('users', 'users.id', '=', 'user_balances.user_id')
        ->select(DB::raw('COALESCE(SUM(amount), 0) as total'))
        ->where('email', $request->email)
        ->where('short_name', $request->iso)
        ->get();

        return response()->json  ([
            'success' => true,
            'message' => 'Crypto Amount',
            'data' => $cryto_amount
        ]);

    }

    public function wagePayments(Request $request){

        $this->validate($request, [
            'reinvestment_amount' => 'required',
            'email' => 'required',
            'iso' => 'required',
        ]);

        $myRequest = new Request([
            'email' => $request->email,
            'iso' => $request->iso,
        ]);
        
        $amount_validation = $this->validateCryptoAmount($myRequest);

        $datos = $amount_validation->getData();

        if ($datos->data[0]->total >= $request->reinvestment_amount) {

            $user = User::where('email', $request->email)->first();

            $coin = PaySettings::where('short_name', $request->iso)->first();
   
            $deposit = new Deposits();

            $deposit->user_id = $user->id;
            $deposit->type_id = $request->type_id;
            $deposit->deposit_date = Carbon::now();
            $deposit->last_pay_day = Carbon::now();
            $deposit->status = 'on';
            $deposit->q_pays = 0;
            $deposit->amount = $request->reinvestment_amount;
            $deposit->actual_amount = $request->reinvestment_amount;
            $deposit->ec = $coin->id;
            $deposit->type = 'reinvestment';
            
    
            if ($deposit->save())
                return response()->json([
                    'success' => true,
                    'message' => 'Deposito realizado corretamente.',
                    'data' => $deposit
                ]);
            else
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit not added'
                ], 500);

        }else{
            echo 'no';
        }

    }
}
