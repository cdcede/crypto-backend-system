<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\History;
use App\Models\SessionOauth;
use App\Models\Deposits;
use Hexters\CoinPayment\CoinPayment;
use DB;
use App\Models\UserBalances;


class testController extends Controller
{
    public function testDayCron(Request $request){
            /* get the holiday */

            $holidays = DB::table('holidays')
            ->select('hd')
            ->where(DB::raw("to_char(holidays.hd,'MM:dd')"), '=', date("m:d"))
            ->get();
            /* if today is not the holiday */
            if (count($holidays)==0) {
                $deposits =  DB::table('deposits')
                ->join('plans','deposits.plan_id','=','plans.id')
                ->select(
                    'deposits.id',
                    'deposits.amount',
                    'deposits.pay_id',
                    'plans.percent',
                    'plans.q_days',
                    'deposits.user_id',
                    'deposits.q_pays',
                    'plans.days',
                    'plans.withdrawel_mondly',
                    'plans.return_capital'
                    )
                ->where(DB::raw("to_char(deposits.created_at,'HH24')"), '=', date("H"))
                ->where(DB::raw("to_char(deposits.updated_at,'YYYY:MM:dd')"), '<>',  date("Y:m:d"))
                ->where('plans.period','D')
                ->where('deposits.status','on')
                ->get();
                  
                foreach ($deposits as $deposit) {
                    $dow = date('w', strtotime( Carbon::now()));
                    
                    if (in_array($dow,  json_decode($deposit->days))== 1) {
                        $earning = $deposit->amount * ($deposit->percent / 100);
                        $history = new History();
                        $history->user_id = $deposit->user_id;
                        $history->deposit_id = $deposit->id;
                        $history->pay_id = $deposit->pay_id;
                        $history->amount = $earning;
                        if($deposit->withdrawel_mondly){
                            $history->type = 'Earning';
                            $history->description = 'Daily earning';
                        }else{
                            $history->type = 'Earning_reserved';
                            $history->description = 'Reserved daily profit';
                        }
                        $history->actual_amount = $earning;
                    
                        if ($history->save()) {
                            $days = $deposit->q_pays + 1;
                            $depositFind = Deposits::find($deposit->id);
                            $depositFind->last_pay_day  = Carbon::now();
                            $depositFind->q_pays = $days;
                           if($deposit->q_days == $deposit->q_pays+1){
                                $depositFind->status = 'off';

                                if(!$deposit->withdrawel_mondly){
                                    $history = new History();
                                    $history->user_id = $deposit->user_id;
                                    $history->deposit_id = $deposit->id;
                                    $history->pay_id = $deposit->pay_id;
                                    $pay_all = DB::table('histories')
                                    ->select(DB::raw('COALESCE(SUM(amount), 0) as total'))
                                    ->where('type', '=', 'Earning_reserved')
                                    ->where('deposit_id', '=', $deposit->id)
                                    ->get();
                                    $history->amount = $pay_all[0]->total;
                                    $history->actual_amount = $pay_all[0]->total;
                                    $history->type = 'Earning_released';
                                    $history->description = 'Released earning';
                                    $history->save();
                                    
                                }
                            }
                            $depositFind->save();
                        }
                    }
                }
                         # code...

            }
        }
        public function testSession(){
               $sessions = SessionOauth::select('oauth_access_tokens.*','users.last_access_time')
            ->leftjoin('users','users.id','oauth_access_tokens.user_id')
            ->where('revoked', false)
            ->get();

            foreach ($sessions as $session) {
                if($session->last_access_time == null){

                    SessionOauth::where('user_id',$session->user_id)->update([
                        'revoked'=>true
                    ]);
                }else{
                $date_now = date("Y-m-d h:i:s");
                $variable = $session->last_access_time;
                $diff = abs(strtotime($date_now) - strtotime($session->last_access_time));
                $years = floor($diff / (365*60*60*24)); 
                $months = floor(($diff - $years * 365*60*60*24)
                / (30*60*60*24)); 
                $days = floor(($diff - $years * 365*60*60*24 - 
                    $months*30*60*60*24)/ (60*60*24));
                    $hours = floor(($diff - $years * 365*60*60*24 
            - $months*30*60*60*24 - $days*60*60*24)
                                        / (60*60)) + $days* 24 + $months*30;

                                        
 
                if($hours>7){

                     SessionOauth::where('user_id',$session->user_id)->update([
                        'revoked'=>true
                    ]);
                    /* $sessionoauth->revoked = true;
                    $sessionoauth->save(); */
                }
            }
            }
        }

        public function testMondlyCron(Request $request){


            /* get the holiday */
            $holidays = DB::table('holidays')
            ->select('hd')
            ->where(DB::raw("to_char(holidays.hd,'MM:dd')"), '=', date("m:d"))
            ->get();
            /* if today is not the holiday */
            if (count($holidays)==0) {
                $deposits =  DB::table('deposits')
                ->join('plans','deposits.plan_id','=','plans.id')
                ->select(
                    'deposits.id',
                    'deposits.amount',
                    'deposits.pay_id',
                    'plans.percent',
                    'plans.q_days',
                    'deposits.user_id',
                    'deposits.q_pays',
                    'plans.days',
                    'plans.withdrawel_mondly',
                    'plans.return_capital'
                    )
                ->where(DB::raw("to_char(deposits.created_at,'HH24')"), '=', date("H"))
                ->where(DB::raw("to_char(deposits.updated_at,'YYYY:MM')"), '<>',  date("Y:m"))
                ->where('plans.period','M')
                ->where('deposits.status','on')
                ->get();

                foreach ($deposits as $deposit) {
                    $dow = date('w', strtotime( Carbon::now()));
                    
                    if (in_array($dow,  json_decode($deposit->days))== 1) {
                        $earning = $deposit->amount * ($deposit->percent / 100);
                        $history = new History();
                        $history->user_id = $deposit->user_id;
                        $history->deposit_id = $deposit->id;
                        $history->pay_id = $deposit->pay_id;
                        $history->amount = $earning;
                        if($history->withdrawel_mondly){
                            $history->type = 'Earning';
                            $history->description = 'Monthly earning';
                        }else{
                            $history->type = 'Earning_reserved';
                            $history->description = 'Reserved Monthly profit';
                        }
                        $history->actual_amount = $earning;
                    
                        if ($history->save()) {
                            $days = $deposit->q_pays + 1;
                            $depositFind = Deposits::find($deposit->id);
                            $depositFind->last_pay_day  = Carbon::now();
                            $depositFind->q_pays = $days;
                           if($deposit->q_days == $deposit->q_pays+1){
                                $depositFind->status = 'off';

                                if(!$deposit->withdrawel_mondly){
                                    $history = new History();
                                    $history->user_id = $deposit->user_id;
                                    $history->deposit_id = $deposit->id;
                                    $history->pay_id = $deposit->pay_id;
                                    $pay_all = DB::table('histories')
                                    ->select(DB::raw('COALESCE(SUM(amount), 0) as total'))
                                    ->where('type', '=', 'Earning_reserved')
                                    ->where('deposit_id', '=', $deposit->id)
                                    ->get();
                                    $history->amount = $pay_all[0]->total;
                                    $history->actual_amount = $pay_all[0]->total;
                                    $history->type = 'Earning_released';
                                    $history->description = 'Released earning';
                                    $history->save();
                                    
                                }
                            }
                            $depositFind->save();
                        }
                    }
                }
            }
        }
}
