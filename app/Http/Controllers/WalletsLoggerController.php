<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\WalletsLogger;

class WalletsLoggerController extends Controller
{
    
    public function walletsLogger(Request $request){
        
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $column_name = $request->column_name??'username';
        $to_date = $request->to_date;
        $word_filter_array = $request->word_filter_array;
        $order = $request->order??'desc';

            $wallets = WalletsLogger::with(['user', 'pay'])
            ->select('wallets_loggers.*','users.username','users.email','pay_settings.short_name')
            ->join('users', 'users.id', '=', 'wallets_loggers.user_id')
            ->join('pay_settings', 'pay_settings.id', '=', 'wallets_loggers.pay_settings_id')
            ->wordFilter($word_filter,['username','first_name','last_name'],'wallets_loggers.created_at',$from_date,$to_date)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $wallets
        ]);
       
    }

}
