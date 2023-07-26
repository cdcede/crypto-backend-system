<?php

namespace App\Exports;
use App\Models\User;
use App\Models\History;
use DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class WithdrawExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;
    private $headers;
    private $variables;
    private $word_filter;
    private $from_date;
    private $to_date;
    private $report_type;
    private $column_name;
    private $order;
    private $join;
    private $columns_name;
    private $wheres;
    private $payment;
    private $typeTransaction;
    public function __construct($headers,$word_filter_array,$word_filter,$from_date = null,$to_date = null,$report_type,$column_name = null,$order = null,$join = null,$columns_name = null,$wheres = null,$payment = null,$typeTransaction = null)
    {
        $this->headers = $headers;
        $this->word_filter_array = $word_filter_array;
        $this->word_filter = $word_filter;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->report_type = $report_type;
        $this->column_name = $column_name;
        $this->order = $order;
        $this->join = $join;
        $this->columns_name = $columns_name;
        $this->wheres = $wheres;
        $this->payment = $payment;
        $this->typeTransaction = $typeTransaction;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            //'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            //'C'  => ['font' => ['size' => 16]],
        ];
    }
    public function sheets(): array
    {
        return $this->headers;
    }


    
    public function headings(): array
    {
      /*   return [
            'id'
        ]; */
        return  $this->columns_name;
    }

    public function query()
    {
        $searchTerm = $this->word_filter;
        $variables = $this->word_filter_array;

        $query = DB::table($this->report_type )->select($this->headers);
        if($this->join!=null){
            foreach ($this->join as $join) {
                $query->join($join[0],$join[1],$join[2]);
            }

        }
       
        if($this->wheres!=null){
            foreach ($this->wheres as $where) {
                $query->where(DB::raw('UPPER('.$where[0].')'), 'like', "%" . Strtoupper($where[1]) . "%");
                if($where[1] === 'Withdraw_pending' || $where[1] === 'Withdrawal'){
                    $query->join('user_wallets',function($join){
                        $join->on('user_wallets.user_id','=','users.id')
                            ->on('user_wallets.pay_settings_id','=','pay_settings.id');
                    });
                }
            }

        }
        if($this->report_type!='oauth_access_tokens' && $this->report_type!='coinpayment_transactions'){
            $query->where($this->report_type.'.deleted_at',null);
        }

        if($this->report_type==='histories'){
            $query->where('type','like','%'.$this->typeTransaction.'%');
            $query->where('name','like','%'.$this->payment.'%');
        }

        if($variables!=null){
            $query->where(function($query) use($searchTerm,$variables){
                $i = 0;
                foreach ($variables as $v) {
                    ($i==0)?$query->where(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%")
                    :$query->orWhere(DB::raw('UPPER('.$v.')'), 'like', "%" . Strtoupper($searchTerm) . "%");
                    $i++;
                }
            });
        }
       
        if($this->from_date!=null){
            $query->whereBetween($this->report_type.'.created_at',[$this->from_date.' 00:00:00',$this->to_date.' 23:59:59']);
        }
       return  $query->orderBy( $this->column_name,  $this->order);


/*         if ($this->data['report_type'] == 'withdraw') {
            return DB::table('histories as h')->join('users as u', 'u.id' , '=', 'h.user_id')
            ->join('pay_settings as p', 'p.id' , '=', 'h.pay_id')
            ->join('user_wallets as uw',function($join){
                $join->on('uw.user_id','=','u.id')
                    ->on('uw.pay_settings_id','=','p.id');
            })
            ->select('h.created_at','h.type','h.actual_amount','u.username','u.first_name','u.last_name',
            'u.email', 'p.short_name','uw.wallet')
            ->where('h.type', 'Withdraw_pending')
            //->whereBetween('h.created_at',['2022-04-01','2022-04-30 23:59:59'])
            ->whereBetween('h.created_at',[$this->data['from_date'],$this->data['to_date']])
            ->orderBy('h.id');
        } */
       
    }
    public function map($bulk): array
    {
       /*  return [
            $bulk->id,
            $bulk->name,
            $bulk->email,
            Date::dateTimeToExcel($bulk->created_at),
            Date::dateTimeToExcel($bulk->updated_at),
        ]; */
    }
}
