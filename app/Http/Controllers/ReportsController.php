<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;
use App\Exports\UsersExport;
use App\Exports\WithdrawExport;
use Maatwebsite\Excel\Facades\Excel;
use Storage;
use PDF;

class ReportsController extends Controller
{
    public function worldMap(){
        $world_map = DB::table('users')
        ->rightJoin('world_countries', 'users.country', '=', 'world_countries.id')
        ->select(DB::raw('count(users.country) as total'),'world_countries.id', 'world_countries.code')
        ->groupBy('world_countries.id')->get();
        $new_world_map = [];
        foreach($world_map as $country){
            array_push($new_world_map,[$country->code, $country->total]); 
        }
        return $new_world_map;
    }


    public function worldCities(Request $request){
        $world_cities = DB::table('users')
        ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
        ->join('world_countries','world_countries.id','=','world_cities.country_id')
        ->select(DB::raw('count(users.country) as total'),'world_cities.id', 'world_cities.code')
        ->where('world_countries.code', $request->country_id)
        ->groupBy('world_cities.id')->get();
        $new_world_cities = [];
        foreach($world_cities as $city){
            array_push($new_world_cities,[$city->code, $city->total]); 
        }
        return  $new_world_cities ;
    }
    

    public function worldMapMoreInvestment(){
        $world_map = DB::table('deposits')
        ->join('users','users.id','=','deposits.user_id')
        ->rightJoin('world_countries', 'users.country', '=', 'world_countries.id')
        ->select(DB::raw('count(user_id) as amount_deposit'),'world_countries.id', 'world_countries.code')
        ->groupBy('world_countries.id') 
        ->get();
        $new_world_map = [];
        foreach($world_map as $country){
            array_push($new_world_map,[$country->code, $country->amount_deposit]); 
        }
        return $new_world_map;
    }

    public function worldMapAmountInvestment(){
        $world_map = DB::table('deposits')
        ->join('users','users.id','=','deposits.user_id')
        ->rightJoin('world_countries', 'users.country', '=', 'world_countries.id')
        ->select(DB::raw('sum(COALESCE(deposits.amount, 0)) as amount_of_money'),'world_countries.id', 'world_countries.code')
        ->groupBy('world_countries.id') 
        ->get();
        $new_world_map = [];
        foreach($world_map as $country){
            array_push($new_world_map,[$country->code, floatval($country->amount_of_money)]); 
        }
        return $new_world_map;
    }

    public function worldCitiesMoreInvestment(Request $request){
        $world_cities = DB::table('deposits')
        ->join('users','users.id','=','deposits.user_id')
        ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
        ->join('world_countries','world_countries.id','=','world_cities.country_id')
        ->select(DB::raw('count(user_id) as amount_deposit'),'world_cities.id', 'world_cities.code')
        ->where('world_countries.code', $request->country_id)
        ->groupBy('world_cities.id')->get();

        $new_world_cities = [];
        foreach($world_cities as $country){
            array_push($new_world_cities,[$country->code, $country->amount_deposit]); 
        }
        return $new_world_cities;
    }

    public function worldCitiesAmountInvestment(Request $request){
        $world_cities = DB::table('deposits')
        ->join('users','users.id','=','deposits.user_id')
        ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
        ->join('world_countries','world_countries.id','=','world_cities.country_id')
        ->select(DB::raw('sum(COALESCE(deposits.amount, 0)) as amount_of_money'),
        'world_cities.id',
        'world_cities.code')
        ->where('world_countries.code', $request->country_id)
        ->groupBy('world_cities.id')->get();
        $new_world_cities = [];
        foreach($world_cities as $country){
            array_push($new_world_cities,[$country->code, floatval($country->amount_of_money)]); 
        }
        return $new_world_cities;
    }

    public function withdrawPending(Request $request) 
    {
        
        $this->validate($request, [
            //'status' => 'required',
            'report_type' => 'required',
        ]);

        $data = [
            'status' => $request->status,
            'report_type' => $request->report_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date
            //'activation_code' => $activation_code
        ];
        
        /* return  Excel::download(new UsersExport, 'users.xlsx'); */
        Excel::store(new WithdrawExport($data), 'exports/'.$request->report_type.'.xlsx');

        if (Storage::disk('local')->exists('exports/'.$request->report_type.'.xlsx')) {
            
            $fileFront = 'exports/'.$request->report_type.'.xlsx';

            $full_path_front = Storage::path($fileFront);

            $base64Front = base64_encode(Storage::get($fileFront));
            
            $file_data_front = 'data:'.mime_content_type($full_path_front) . ';base64,' . $base64Front;

            return response()->json([
                'success' => true,
                'message' => 'Archivo generado con exito.',
                'data' => $file_data_front,
            ], 200);

        }else {
            return response()->json([
                'error' => 'No existen los archivos en el servidor.'
            ], 422);
        }

    }

    public function heatmap_register(Request $request){

        $world_cities = DB::table('users')
        ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
        ->join('world_countries','world_countries.id','=','world_cities.country_id')
        ->select(DB::raw('count(users.country) as total'),
        DB::raw("to_char(users.created_at,'D') as day" ) ,
        DB::raw("to_char(users.created_at,'hh') as hour"))
        ->groupBy("day")
        ->groupBy("hour")
        ->get();


        $world_citie_return = [];
        for ($j=1; $j <= 24; $j++) { 
            for ($i=1; $i <= 7; $i++) { 
                $back = true;
                    foreach ($world_cities as $world_citie) {
                        if($i == intval($world_citie->day) && $j == intval($world_citie->hour) && $back){
                            $back = false;
                            array_push($world_citie_return, [$j-1,$i-1,number_format($world_citie->total, 2)]);
                        }
                    }
                if($back){
                    array_push($world_citie_return, [$j-1,$i-1,0]);
                }
            }
        }

        return $world_citie_return;
    }

    public function heatmap_deposit(Request $request){
        $world_cities = DB::table('deposits')
       ->join('users','users.id','=','deposits.user_id')
       ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
       ->join('world_countries','world_countries.id','=','world_cities.country_id')
       ->select(DB::raw('sum(COALESCE(deposits.amount, 0)) as amount_of_money'),
       DB::raw("to_char(deposits.created_at,'D') as day" ) ,
       DB::raw("to_char(deposits.created_at,'hh') as hour"))
       ->groupBy("day")
       ->groupBy("hour")
       ->get();
       
       $world_citie_return = [];
       for ($j=1; $j <= 24; $j++) { 
           for ($i=1; $i <= 7; $i++) {
               $back = true;
                   foreach ($world_cities as $world_citie) {
                       if($i == intval($world_citie->day) && $j == intval($world_citie->hour) && $back){
                           $back = false;
                           array_push($world_citie_return, [$j-1,$i-1,number_format($world_citie->amount_of_money, 2)]);
                       }
                   }
               if($back){
                   array_push($world_citie_return, [$j-1,$i-1,0]);
               }
           }
       }
       return $world_citie_return;
   }

   public function column_register(Request $request){
    $world_cities = DB::table('users')
    ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
    ->join('world_countries','world_countries.id','=','world_cities.country_id')
    ->select(DB::raw('count(users.country) as total'),
    DB::raw("to_char(users.created_at,'mm') as month" ))
    ->groupBy("month")
    ->take(6)
    ->get();
    $world_citie_return = [];
    for ($j=0; $j <= 11; $j++){
        $back = true;
        foreach ($world_cities as $world_citie){
            if($j == intval($world_citie->month)){
                $back = false;
                array_push($world_citie_return, $world_citie->total);
            }
           
        }
        if($back){
            array_push($world_citie_return, 0);
        }
    }
    return response()->json([
        'success' => true,
        'data' => $world_citie_return
    ], 200);
    }

   public function column_deposit(Request $request){
     $world_cities = DB::table('deposits')
   ->join('users','users.id','=','deposits.user_id')
   ->rightJoin('world_cities', 'users.state', '=', 'world_cities.id')
   ->join('world_countries','world_countries.id','=','world_cities.country_id')
   ->select(DB::raw('sum(COALESCE(deposits.amount, 0)) as amount_of_money'),
   DB::raw("to_char(users.created_at,'mm') as month" ))
   ->groupBy("month")
   ->get();
   
   $world_citie_return = [];
   for ($j=0; $j <= 11; $j++){
    $back = true;
    foreach ($world_cities as $world_citie){
        if($j == intval($world_citie->month)){
            $back = false;
            array_push($world_citie_return, $world_citie->amount_of_money);
        }
       
    }
    if($back){
        array_push($world_citie_return, 0);
    }
    }


    return response()->json([
        'success' => true,
        'data' => $world_citie_return
    ], 200);
}





public function line_deposit(Request $request){
     $world_cities = DB::table('pay_settings')
    ->join('deposits','deposits.pay_id','=','pay_settings.id')
    ->select(DB::raw('sum(COALESCE(deposits.amount, 0)) as amount_of_money')
    ,'pay_settings.name')
    ->groupBy("pay_settings.name")
    ->get();

    $world_citie_return = [];
    $world_citie_name_return = [];

    foreach ($world_cities as $world_citie){
        array_push($world_citie_return, $world_citie->amount_of_money);     
        array_push($world_citie_name_return, $world_citie->name);     
    }
    $data_return = [
        'data' => $world_citie_return,
        'names'=> $world_citie_name_return
    ];

    return response()->json([
        'success' => true,
        'data' => $data_return
    ], 200);
}


    public function pdf(Request $request){
        
        $pdf = \App::make('snappy.pdf.wrapper');
        $pdf->loadHTML('<h1>Test</h1>');
        return $pdf->inline('test.pdf');

        return PDF::loadFile('https://wagedollar.io')->inline('github.pdf');

        $pdf = PDF::loadView('pdf.invoice', $data);
        return $pdf->download('invoice.pdf');

    }

    public function excelReport(Request $request){
    
        $word_filter=$request->word_filter;
        $report_type=$request->report_type;
        $join=$request->join;
        $typeTransaction=$request->typeTransaction;
        $payment=$request->payment;

        $word_filter_array=$request->word_filter_array;
        $from_date=$request->from_date;
        $to_date=$request->to_date;
        $columns=$request->columns;
        $columns_name=$request->columns_name;
        $wheres=$request->wheres;
        switch ($report_type) {
            case 'coinpayment_transactions':
                $name = 'deposits';
                break;
            
            default:
            $name=$report_type;
                break;
        }
        $noExist = true;
        
        foreach ($columns as $key => $column) {
            switch ($column) {
                case 'activity_amount':
                    $columns[$key] =  'amount';
                break;
                case 'wallet_withdraw':
                    $columns[$key] =  'wallet';
                break;
                case 'activity_currency':
                    $columns[$key] =  'short_name';
                break;
                case 'created_at':
                    $noExist = false;
                    $columns[$key] =  $report_type.'.created_at';
                break;
                case 'id':
                    $columns[$key] =  $report_type.'.id';
                break;
                case 'Actions':
                   unset($columns[$key]);
                   unset($columns_name[$key]);
                break;
                case 'view':
                    unset($columns[$key]);
                    unset($columns_name[$key]);
                 break;
                case 'actions':
                    unset($columns[$key]);
                    unset($columns_name[$key]);
                 break;
                case 'id_document':
                    unset($columns[$key]);
                    unset($columns_name[$key]);
                 break;
                 
            }
        }
        if($report_type == 'users'){
            foreach ($columns as $key => $column) {
                switch ($column) {
                     case 'name':
                        unset($columns[$key]);
                        unset($columns_name[$key]);
                        array_splice($columns, $key, 0, 'first_name');
                        array_splice($columns, $key, 0, 'last_name');
                        array_splice($columns_name, $key, 0, 'first_name');
                        array_splice($columns_name, $key, 0, 'last_name');
                     break;
                }
            }
        
        
        foreach ($word_filter_array as $key => $wf) {
            switch ($wf) {
                 case 'name':
                    unset($word_filter_array[$key]);
                    array_splice($word_filter_array, $key, 0, 'first_name');
                    array_splice($word_filter_array, $key, 0, 'last_name');
                 break;
            }
        }
    }
        $columns = array_values($columns);
        $columns_name = array_values($columns_name);
        $word_filter_array = array_values($word_filter_array);

        if($noExist==true){
            array_push($columns,$report_type.'.created_at');
        }

        switch ($request->column_name) {
            case 'activity_amount':
                $column_name =  'name';
            break;
            default:
                $column_name =  $request->column_name??$report_type.'.id';
            break;
        }

 
         $order = $request->order??'desc';




         $file = Excel::store(new WithdrawExport(
            $columns,
            $word_filter_array,
            $word_filter, 
            $from_date, 
            $to_date,
            $report_type,
            $column_name,
            $order,
            $join,
            $columns_name,
            $wheres,
             $payment,
             $typeTransaction)
            , $name.'.xlsx');


            $path = storage_path('app/'.$name.'.xlsx');
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
          /*  return  'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,' . base64_encode($data); */
            $base64 = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.base64_encode($data);
            return response()->json([
                'success' => true,
                'data' => $base64
            ], 200);
          
    }


}
