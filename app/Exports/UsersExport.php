<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Deposits;
use App\Models\History;
use DB;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class UsersExport implements FromQuery
{
    
    use Exportable;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function query()
    {

        if ($this->data['report_type'] == 'deposits') {
            return Deposits::query();
        }elseif ($this->data['report_type'] == 'users') {
            return User::get();
        //}elseif ($this->data['report_type'] == 'Withdraw_pending') {
        }elseif ($this->data['report_type'] == 'withdraw') {
            return History::query()->join('users', 'users.id' , '=', 'histories.user_id')
            ->join('pay_settings', 'pay_settings.id' , '=', 'histories.pay_id')
            ->join('user_wallets',function($join){
                $join->on('user_wallets.user_id','=','users.id')
                    ->on('user_wallets.pay_settings_id','=','pay_settings.id');
            })
            ->select('histories.*', 'users.username','users.first_name','users.last_name','users.email', 
            'pay_settings.name','pay_settings.short_name','pay_settings.icon','user_wallets.wallet')
            ->where('histories.type', 'Withdraw_pending')
            ->whereBetween('histories.created_at',['2022-04-01','2022-04-30']);
            //->whereBetween('histories.created_at','[$this->data['from_date']',$this->data['to_date']]);
        }
        
    }
}
