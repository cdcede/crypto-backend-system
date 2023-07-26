<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserBalances;
use App\Models\Deposits;
use Auth;

class UserBalancesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userBalances = UserBalances::get();
 
        return response()->json([
            'success' => true,
            'data' => $userBalances
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
            'level' => 'required'
        ]);
 
        $userBalances = new UserBalances();
        $userBalances->user_id = $request->user_id;
        $userBalances->pay_id = $request->pay_id;
        $userBalances->amount = $request->amount;
        $userBalances->type = $request->type;
 
        if ($userBalances->save())
            return response()->json([
                'success' => true,
                'data' => $userBalances->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'User Balances not added'
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
        $userBalances = UserBalances::find($id);
 
        if (!$userBalances) {
            return response()->json([
                'success' => false,
                'message' => 'User Balances not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $userBalances->toArray()
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
    public function update(Request $request, $id)
    {
        $userBalances = UserBalances::find($id);
 
        if (!$userBalances) {
            return response()->json([
                'success' => false,
                'message' => 'User Balances not found'
            ], 400);
        }
 
        $updated = $userBalances->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'User Balances can not be updated'
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
        $userBalances = UserBalances::find($id);
 
        if (!$userBalances) {
            return response()->json([
                'success' => false,
                'message' => 'User Balances not found'
            ], 400);
        }
 
        if ($userBalances->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User Balances can not be deleted'
            ], 500);
        }
    }

    public function showUserBalances(){

        $user = Auth::user();

        //last deposit
        $last_deposit = Deposits::select('created_at')
        ->where('user_id', $user->id)
        ->where('status', 'on')
        ->orderBy('id', 'desc')
        ->first();
        //select created_at from deposits where user_id = 5 ORDER BY Id DESC LIMIT 1;

        //active deposit
        $active_deposit = Deposits::where('user_id', $user->id)
        ->where('status', 'on')
        ->sum('amount');

      


        //total deposits
        $total_deposits = UserBalances::where('user_id', $user->id)
        ->where('type', 'Add_funds')
        ->sum('amount');

       /*  $total_deposits = UserBalances::where('user_id', $user->id)
        ->where('type', 'Add_funds')
        ->orderBy('id', 'desc')
        ->first(); */
        //select * from user_balances where user_id = 5 and type = 'add_funds' ORDER BY Id DESC LIMIT 1;
        $earned_reserved = UserBalances::where('user_id', $user->id)
        ->where('type', 'Earning_reserved')
        ->sum('amount');
        $earned_released = UserBalances::where('user_id', $user->id)
        ->where('type', 'Earning_released')
        ->sum('amount');
        $earned_reserved =  $earned_reserved - $earned_released ;
        //Earned total
        $earned_total = UserBalances::where('user_id', $user->id)
        ->where('type', 'Earning')
        ->sum('amount');

        //select * from user_balances where user_id = 5 and type = 'earned_total' ORDER BY Id DESC LIMIT 1;

        //Withdraw pending
        $withdraw_pending = UserBalances::where('user_id', $user->id)
        ->where('type', 'Withdraw_pending')
        ->sum('amount');
        //->orderBy('id', 'desc')
        //->first();
        //select * from user_balances where user_id = 5 and type = 'withdraw_pending' ORDER BY Id DESC LIMIT 1;
        
        
        //Withdraw total
        $withdraw_total = UserBalances::where('user_id', $user->id)
        ->where('type', 'Withdrawal')
        //->sum('amount');
        ->orderBy('id', 'desc')
        ->first();
        //select * from user_balances where user_id = 5 and type = 'withdraw_total' ORDER BY Id DESC LIMIT 1;

        //enable_commission
        $enable_commission = UserBalances::where('user_id', $user->id)
        ->where('type', 'Enable_commission')
        ->sum('amount');
        //->orderBy('id', 'desc')
        //->first();
        //select * from user_balances where user_id = 5 and type = 'enable_commission' ORDER BY Id DESC LIMIT 1;

        //pending_commission
        $pending_commission = UserBalances::where('user_id', $user->id)
        ->where('type', 'Pending_commission')
        ->sum('amount');
        //->orderBy('id', 'desc')
        //->first();
        //select * from user_balances where user_id = 5 and type = 'pending_commission' ORDER BY Id DESC LIMIT 1;

        $balance = UserBalances::where('user_id', $user->id)
        ->where(function($query) {
            $query->where('type','=','Earning')
                ->orWhere('type','=','Earning_released')
                ->orWhere('type','=','Withdrawel')
                ->orWhere('type','=','Enable_commission');
             }) ->sum('amount');
        
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => [
                'active_deposit' => $active_deposit??0,
                'last_deposit' => $last_deposit->created_at??0,
                'total_deposits' => $total_deposits??0,
                'earned_total' => $earned_total??0,
                'earned_reserved' => $earned_reserved??0,
                'earned_released' => $earned_released??0,
                'withdraw_pending' => $withdraw_pending??0,
                'withdraw_total' => $withdraw_total??0,
                'enable_commission' => $enable_commission??0,
                'pending_commission' => $pending_commission??0,
                'balance' => $balance??0
            ]
        ]);
        
    }
}
