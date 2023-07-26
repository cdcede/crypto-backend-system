<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Models\History;
use App\Models\Deposits;
use App\Models\SessionOauth;
use Hexters\CoinPayment\CoinPayment;
use DB;
use App\Models\UserBalances;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            
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
            }
        })->cron('* * * * *');


        $schedule->call(function () {
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
                         if($deposit->withdrawel_mondly){
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
        })->cron('* * * * *');

        $schedule->call(function () {
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

                                        
 
                if($hours>2){

                     SessionOauth::where('user_id',$session->user_id)->update([
                        'revoked'=>true
                    ]);
                    /* $sessionoauth->revoked = true;
                    $sessionoauth->save(); */
                }
            }
        }
       })->cron('* * * * *');

        //SOLO DESACTIVAR PARA DEVELOPMENT
       
        $schedule->call(function () {
            $ids_transactions =  DB::table('coinpayment_transactions')
            ->select('txn_id')
            ->where('status','0')
            ->orWhere('status','1')
            ->get();
            foreach ($ids_transactions as $id_transaction) {
                try{
                    CoinPayment::getstatusbytxnid($id_transaction->txn_id);
                }catch (Exception $e) {

                }
            }
        })->cron('* * * * *');

        //SOLO DESACTIVAR PARA DEVELOPMENT

   
        /* TESTING */
        /* $schedule->call(function () {
            DB::table('cities')->insert([
                'country_id' => '1',
                'state_id' => 1,
                'name' => '0.0.0.0'
            ]);
        })->cron('* * * * *');

        $schedule->call(function () {
            DB::table('cities')->insert([
                'country_id' => '1',
                'state_id' => 1,
                'name' => '0.0.0.1'
            ]);
        })->cron('* * * * *');

        $schedule->call(function () {
            DB::table('cities')->insert([
                'country_id' => '1',
                'state_id' => 1,
                'name' => '0.0.0.2'
            ]);
        })->cron('14 * * * *');

        $schedule->command('inspire')->cron('1 * * * *');
        $schedule->call(function () {
            DB::table('tracks')->insert([
                'previous_url' => '132',
                'history_number' => 1,
                'ip' => '0.0.0.0',
                'device' => 'NO'
            ]);
        })->everyMinute(); */
        
    }
    
    
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
