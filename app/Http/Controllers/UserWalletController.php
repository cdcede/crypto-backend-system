<?php

namespace App\Http\Controllers;

use App\Models\UserWallet;
use App\Models\PaySettings;
use App\Models\WalletsLogger;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Notifications\UpdateWalletNotification;

class UserWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $userWallets = \DB::table('user_wallets')->rightJoin('pay_settings', 'user_wallets.pay_settings_id', '=', 'pay_settings.id')
        ->select('user_wallets.*', 'pay_settings.name', 'pay_settings.short_name', 'pay_settings.icon')
        ->where('pay_settings.pay_type', 'C')->where('user_id', $user->id)->orderBy('pay_settings_id', 'desc')->get();

        return response()->json($userWallets);

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
       /*  $this->validate($request, [
            'pay_settings_id' => 'required',
            'wallet' => 'required',
        ]); */

        $user = Auth::user();


        
        /* $userWallets = UserWallet::updateOrCreate(
            ['id' => $user->id],
            [
                'user_id' => $user->id,
                'pay_settings_id' => $request->pay_settings_id,
                'wallet' => $request->wallet,
                'status' => true
            ]
        ); */

        $wallets = UserWallet::where('user_id', $user->id)->orderBy('pay_settings_id', 'desc')->get();

        //return $wallets;

        if ($wallets) {

            for ($i=0; $i < count($wallets); $i++) { 
                
             $wallet = $wallets[$i];             
             $wallet->wallet = $request->walletsUser[$i]["wallet"];
             $wallet->save();
             
            }

            return response()->json([
                'success' => true,
                'data' => $wallets,
                'message' => 'Wallet actualizada correctamente.'
            ]);
        }
    }

    public function createAndUpdateWallet(Request $request){

        try {
            
          
            $user = Auth::user();

            $wallets = UserWallet::where('user_id', $user->id)
            ->where('status', true)
            ->orderBy('pay_settings_id', 'desc')
            ->get();

            //return $wallets;

            /* $wallets = \DB::table('user_wallets')->rightJoin('pay_settings', 'user_wallets.pay_settings_id', '=', 'pay_settings.id')
            ->select('user_wallets.*', 'pay_settings.name', 'pay_settings.short_name', 'pay_settings.icon')
            ->where('pay_settings.status', true)->where('user_id', $user->id)->orderBy('user_wallets.id', 'desc')->get(); */

            /* return response()->json([
                'success' => true,
                'walletss' => $walletss,c
                'wallets' => $wallets
            ]); */
          
            if ($wallets) {
                for ($i=0; $i < count($wallets); $i++) { 
                $wallet = UserWallet::where('id',$wallets[$i]);

                $wallet = $wallets[$i];

                //print_r ($request->walletsUser[$i]["wallet"]);
               
                if (DB::table('user_wallets')
                ->where('wallet', $request->walletsUser[$i]["wallet"])
                ->where('user_id','!=',$user->id)->exists()
                && ($request->walletsUser[$i]["wallet"] != null || $request->walletsUser[$i]["wallet"] != "")
                ) {
                    return response()->json([
                        'success' => false,
                        'errors' => $message = ["Ya existe la billetera: ".$request->walletsUser[$i]["wallet"]]
                    ], 422);
                }

                $wallet->wallet = $request->walletsUser[$i]["wallet"];
                
                //$billeteras = $wallets[$i];
                
                $wallet->save();
                
                }

                $data = [
                    'user' => $user->first_name." ".$user->last_name,
                    //'wallets' => $billeteras
                ];

                //Send email login notification only if role is different to.
                
                $user->notify(new UpdateWalletNotification($data));

                return response()->json([
                    'success' => true,
                    //'data' => $respArr,
                    'message' => 'Wallet actualizada correctamente.'
                ]);
            }

        } catch (\Exception $e) { // It's actually a QueryException but this works too
            if ($e->getCode() == 23505) {
                // Deal with duplicate key error
                //return $e;
                return response()->json([
                    'success' => false,
                    'errors' => $message = ['Error: No puede haber una billetera duplicada.']
                ],422);
            }
        }
    }

    public function verifyWallets(Request $request){
        
        $user = Auth::user();

        $historie = \DB::table('histories')
        ->where('histories.user_id',  $user->id)
        ->where('histories.type', 'Withdraw_pending')
        ->get();
        
        if(!count($historie)>0){
        
            $wallets = UserWallet::where('user_id', $user->id)->orderBy('pay_settings_id', 'desc')->get();

            if ($wallets) {
                $respArr = [];

                for ($i=0; $i < count($wallets); $i++) { 
                
                $existe = UserWallet::where('wallet', $request->walletsUser[$i]["wallet"])->where('user_id','<>', $user->id)->exists();
                    
                $wallet = UserWallet::where('id',$wallets[$i]);

                $wallet = $wallets[$i];

                $wallet->wallet = $request->walletsUser[$i]["wallet"];
                
                
                if ($existe != 1) {
                    if(UserWallet::where('wallet', $request->walletsUser[$i]["wallet"])->exists() != 1){
                        array_push($respArr,1);
                    }

                }else {
                    array_push($respArr,0);
                }

                
                }

                return response()->json([
                    'success' => true,
                    'data' => $respArr,
                    'message' => 'Wallet verificada correctamente.'
                ]);
            }
        }

        $data = [ 
            'message' => __('A wallet change cannot be made until your withdrawal is confirmed')
          ];
          return response()->json($data, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserWallet  $userWallet
     * @return \Illuminate\Http\Response
     */
    public function show(UserWallet $userWallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserWallet  $userWallet
     * @return \Illuminate\Http\Response
     */
    public function edit(UserWallet $userWallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserWallet  $userWallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserWallet $userWallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserWallet  $userWallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserWallet $userWallet)
    {
        //
    }
}
